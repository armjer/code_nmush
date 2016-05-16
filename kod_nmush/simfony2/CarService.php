<?php

/**
 * Created by Armen Ishkhanyan.
 * Email: armjer@mail.ru
 * Date: 10.08.15 16:20
 */

namespace AppBundle\Services;

use AppBundle\Entity\CarBmwNumber;
use AppBundle\Entity\CarBrand;
use AppBundle\Entity\CarImageMap;
use AppBundle\Entity\CarModel;
use AppBundle\Entity\CarModification;
use AppBundle\Entity\CarParts;
use AppBundle\Entity\CarPartsGroup;
use AppBundle\Entity\CarPartsInfo;
use AppBundle\Entity\CarRelationParts;
use AppBundle\Entity\CarUnit;
use AppBundle\Entity\CarUnitOption;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CarService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CarBrand
     */
    private $brand;

    /**
     * CarService constructor.
     *
     * @var ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    /**
     * @return CarBrand[]|null
     */
    public function getBrands()
    {
        return $this->entityManager->getRepository('Car:Brands')->findAll();
    }

    /**
     * @param CarBrand|null $brand
     * @return array
     */
    public function getSeries($brand = null)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.series_raw id, c1.series name')
            ->distinct()
            ->from('Car:CarModel', 'c1')
            ->join('c1.carBrand', 'c2')
            ->where('c2.id = :brandId')
            ->orderBy('c1.series')
            ->setParameter('brandId' , is_null($brand)? $this->brand->getId(): $brand->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $seriesId
     * @param CarBrand|null $brand
     * @return array
     */
    public function getModelsBySeries($seriesId, $brand = null)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.name')
            ->distinct()
            ->from('Car:CarModel', 'c1')
            ->join('c1.carBrand', 'c2')
            ->where('c2.id = :brandId AND c1.series_raw = :seriesId')
            ->orderBy('c1.name')
            ->setParameters(
                array('brandId' => is_null($brand) ? $this->brand->getId() : $brand->getId(), 'seriesId' => $seriesId)
            );


        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param integer $carBrandId
     * @return array
     */
    public function getModelsByBrand($carBrandId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.id, c1.name')
            ->from('Car:CarModel', 'c1')
            ->where('c1.carBrand = :brandId ')
            ->orderBy('c1.id')
            ->setParameters(array('brandId' => $carBrandId));

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param CarModel $carModel
     * @return array
     */
    public function getModificationsByModel(CarModel $carModel)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.id, c1.steering, c1.gearbox, c1.year, c1.month')
            ->distinct()
            ->from('Car:CarModification', 'c1')
            ->join('c1.carModel', 'c2', 'WITH', 'c2.id = :modelId')
            ->orderBy('c1.steering')
            ->orderBy('c1.gearbox')
            ->orderBy('c1.year')
            ->orderBy('c1.month')
            ->setParameters(array('modelId' => $carModel->getId()));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return CarBrand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param $name
     * @return $this
     * @throws CarServiceException
     */
    public function setBrandName($name)
    {
        /** @var CarBrand $carBrand */
        $carBrand = $this->entityManager->getRepository('Car:CarBrand')->findOneBy(array('name' => strtoupper($name)));
        if (empty($carBrand)) {
            throw new CarServiceException('Car brand is not found');
        }
        $this->setBrand($carBrand);

        return $this;
    }

    /**
     * @param CarBrand $brand
     * @return self
     */
    public function setBrand(CarBrand $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @param $series string
     * @param $model string
     * @param $region string
     * @param $body string
     * @return null|CarModel
     * @throws CarServiceException
     */
    public function getModelByParams($series, $model, $region, $body)
    {
        $carModel = $this->entityManager->getRepository('Car:CarModel')->findOneBy(
            array(
                'series_raw' => $series,
                'name' => str_replace("-", " ", $model),
                'region' => $region,
                'body_raw' => $body,
            )
        );
        if (empty($carModel)) {
            throw new CarServiceException('Car model is not found');
        }

        return $carModel;
    }

    /**
     * @param CarModel $carModel
     * @param $steering string
     * @param $gearbox string
     * @param $year int
     * @param $month int
     * @param $unitCode string
     * @return CarUnit|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUnitByParams(CarModel $carModel, $steering, $gearbox, $year, $month, $unitCode)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1')
            ->from('Car:CarUnit', 'c1')
            ->where('c1.carModel = :model')
            ->andWhere('c1.code = :code')
            ->andWhere('c1.steering = :steering OR c1.steering IS NULL')
            ->andWhere('c1.gearbox = :gearbox OR c1.gearbox IS NULL')
            ->andWhere('c1.date_from >= :date_from OR c1.date_from IS NULL')
            ->andWhere('c1.date_to <= :date_to OR c1.date_to IS NULL')
            ->setParameters(
                array(
                    'model' => $carModel,
                    'steering' => $steering,
                    'gearbox' => $gearbox,
                    'date_from' => $year . $month,
                    'date_to' => $year . $month,
                    'code' => $unitCode,
                )
            );

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param CarModel $carModel
     * @param $steering
     * @param $gearbox
     * @param $year
     * @param $month
     * @param $partsGroupId
     * @return CarUnit[]|null
     */
    public function getUnitsByParams(CarModel $carModel, $steering, $gearbox, $year, $month, $partsGroupId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1')
            ->from('Car:CarUnit', 'c1')
            ->where('c1.carModel = :model')
            ->andWhere('c1.car_parts_group_id = :car_parts_group_id')
            ->andWhere('c1.steering = :steering OR c1.steering IS NULL')
            ->andWhere('c1.gearbox = :gearbox OR c1.gearbox IS NULL')
            ->andWhere('c1.date_from >= :date_from OR c1.date_from IS NULL')
            ->andWhere('c1.date_to <= :date_to OR c1.date_to IS NULL')
            ->setParameters(
                array(
                    'model' => $carModel,
                    'steering' => $steering,
                    'gearbox' => $gearbox,
                    'date_from' => $year . $month,
                    'date_to' => $year . $month,
                    'car_parts_group_id' => $partsGroupId,
                )
            );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param CarModel $carModel
     * @param CarUnit $carUnit
     * @return CarRelationParts[]
     */
    public function getPartsByParams(CarModel $carModel, CarUnit $carUnit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1')
            ->from('Car:CarRelationParts', 'c1')
            ->where('c1.carModel = :model')
            ->andWhere('c1.car_unit_code = :code')
//            ->orderBy('c1.car_option_group_id')
            ->setParameters(array('model' => $carModel, 'code' => $carUnit->getCode()));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $imageId
     * @param CarRelationParts[] $carRelationParts
     * @return CarImageMap[]
     */
    public function getCarImageMap($imageId, $carRelationParts)
    {
        /** @var ImageLocator $imageLocator */
        $imageLocator = $this->container->get('image_locator');
        $partsImage = $imageLocator->get(strtolower($this->getBrand()->getName()), $imageId);

        if ($partsImage === null) {
            return null;
        }

        /** @var CarRelationParts[] $partsRef */
        $partsRef = [];
        foreach ($carRelationParts as &$relation) {
            $partsRef[$relation->getImageNumber()] = $relation;
        }

        $realSize   = [$partsImage->getWidth(), $partsImage->getHeight()];
        $webSize    = [650, 460];
        $deltaW     = $webSize[0] / $realSize[0];
        $deltaH     = $webSize[1] / $realSize[1];

        /** @var CarImageMap[] $imageMap */
        $imageMap = $this->entityManager->getRepository('Car:CarImageMap')->findBy(array('image_id' => $imageId));
        foreach ($imageMap as &$coordinate) {
            $newTopLeftX = intval($coordinate->getTopLeftX() * $deltaW);
            $coordinate->setTopLeftX($newTopLeftX);
            $newTopLeftY = intval($coordinate->getTopLeftY() * $deltaH);
            $coordinate->setTopLeftY($newTopLeftY);
            $newBottomRightX = intval($coordinate->getBottomRightX() * $deltaW);
            $coordinate->setBottomRightX($newBottomRightX);
            $newBottomRightY = intval($coordinate->getBottomRightY() * $deltaH);
            $coordinate->setBottomRightY($newBottomRightY);

            if (isset($partsRef[$coordinate->getImageNumber()])) {
                $partsRel = $partsRef[$coordinate->getImageNumber()];
                $coordinate->setCarRelationParts($partsRel);
            }
        }

        return $imageMap;
    }

    /**
     * @param CarModel $carModel
     * @param $partsGroupId int
     * @return null|CarPartsGroup
     */
    public function getPartsGroupByParam(CarModel $carModel, $partsGroupId)
    {
        return $this->entityManager->getRepository('Car:CarPartsGroup')->findOneBy(
            array('carModel' => $carModel, 'id' => $partsGroupId)
        );
    }


    /**
     * @param CarModel $carModel
     * @return null|CarPartsGroup[]
     */
    public function getPartsGroups(CarModel $carModel)
    {
        return $this->entityManager->getRepository('Car:CarPartsGroup')->findBy(
            array('carModel' => $carModel)
        );
    }

    /**
     * @param integer $carBrandId
     * @param integer $modelId
     * @param integer $modifId
     * @param integer $year
     * @return array
     */
    public function searchCar($carBrandId = NULL, $modelId = NULL, $modifId = NULL, $year = NULL )
    {
        $item = Array();
        $pars = Array();
        $str = '';

        if($carBrandId) {
            $pars['brandId'] = $carBrandId;
            $str .= ' AND cb.id = :brandId ';
        }

        if($modelId) {
            $pars['modelId'] = $modelId;
            $str .= '  AND cm.id=:modelId  ';
        }

        if($modifId) {
            $pars['cmodifId'] = $modifId;
            $str .= ' AND cmodif.id=:cmodifId ';
        }

        if($year) {
            $pars['year'] = $year;
            $str .= ' AND cmodif.year=:year ';
        }

        $q =  $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cb.id, cb.name, cm.id as cid, cm.name as cmodel, cmodif.steering, cmodif.year')
            ->from('Car:CarBrand', 'cb')
            ->innerJoin('Car:CarModel','cm', \Doctrine\ORM\Query\Expr\Join::WITH, 'cm.carBrand =cb.id')
            ->innerJoin('Car:CarModification','cmodif', \Doctrine\ORM\Query\Expr\Join::WITH, 'cmodif.carModel = cm.id')
            ->where(" 1>0 $str")
            ->orderBy('cb.id, cm.id')
            ->setParameters($pars)
            ->getQuery();

        $res = $q->getArrayResult();
        if(count($res)) {
            $item['brend'] = $res[0]['name'];
            $item['model'] = $res[0]['cmodel'];
            $item['modification'] = $res[0]['steering'];
            $item['year'] = $res[0]['year'];
        }

        return $item;
    }

    /**
     * @param integer $carModelId
     * @return array
     */
    public function getModificationsByModelId($carModelId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.id, c1.steering')
            ->from('Car:CarModification', 'c1')
            ->where('c1.carModel = :carModelId ')
            ->orderBy('c1.id')
            ->setParameters(array('carModelId' => $carModelId));

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param integer $carModelId
     * @return array
     */
    public function getSteeringByModelId($carModelId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.steering')
            ->distinct()
            ->from('Car:CarModification', 'c1')
            ->where('c1.carModel = :carModelId ')
            ->orderBy('c1.id')
            ->setParameters(array('carModelId' => $carModelId));

        return $queryBuilder->getQuery()->getArrayResult();
    }


    /**
     * @param array $requestArr
     * @return array
     */
    public function getGearbox($requestArr)
    {
        $carModelId = (intval($requestArr['car_model_id'])) ? $requestArr['car_model_id'] : 0;
        $steering = ($requestArr['steering']) ? $requestArr['steering'] : '';

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.gearbox')
            ->distinct()
            ->from('Car:CarModification', 'c1')
            ->where('c1.carModel = :carModelId and c1.steering = :steering ')
            ->orderBy('c1.id')
            ->setParameters(array('carModelId' => $carModelId, 'steering' => $steering ));

        return $queryBuilder->getQuery()->getArrayResult();
    }



    /**
     * @param integer $id
     * @return array
     */
    public function getRegionByModelId($name)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cm.region')
            ->distinct()
            ->from('Car:CarModel', 'cm')
            ->where('cm.name = :name ')
            ->orderBy('cm.name')
            ->setParameters(array('name' => $name));


        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param array $requestData
     * @return array
     */
    public function getBodyByRegion($requestData)
    {
        $item = Array();
        $pars = Array();
        $str = '';

        if(isset($requestData['car_brand_id']) && $requestData['car_brand_id'] ) {
            $pars['car_brand_id'] = $requestData['car_brand_id'];
            $str .= ' AND cm.carBrand = :car_brand_id ';
        }

        if(isset($requestData['series']) && $requestData['series'] ) {
            $pars['series'] = $requestData['series'];
            $str .= ' AND cm.series_raw = :series ';
        }

        if(isset($requestData['name']) && $requestData['name'] ) {
            $pars['name'] = $requestData['name'];
            $str .= ' AND cm.name = :name ';
        }

        if(isset($requestData['regions']) && $requestData['regions'] ) {
            $pars['regions'] = $requestData['regions'];
            $str .= ' AND cm.region = :regions ';
        }

        if(isset($requestData['body']) && $requestData['body'] ) {
            $pars['body'] = $requestData['body'];
            $str .= ' AND cm.body_raw = :body ';
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cm.body_raw, cm.body')
            ->from('Car:CarModel', 'cm')
            ->distinct()
            ->where(" 1>0 $str")
            ->orderBy('cm.region')
            //->groupBy('cm.body_raw')
            ->setParameters($pars);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param array $requestData
     * @return array
     */
    public function getYearsByModelSteeringGearbox($requestData)
    {
        $pars = array();
        $str = '';

        if (!empty($requestData['car_model_id'])) {
            $pars['carModel'] = $requestData['car_model_id'];
            $str .= ' AND cm.carModel = :carModel';
        }

        if (!empty($requestData['steering']) && $requestData['steering'] != 'N' ) {
            $pars['steering'] = $requestData['steering'];
            $str .= ' AND cm.steering = :steering';
        }

        if (!empty($requestData['gearbox'])  && $requestData['gearbox'] != 'N' ) {
            $pars['gearbox'] = $requestData['gearbox'];
            $str .= ' AND cm.gearbox = :gearbox';
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cm.year')
            ->distinct()
            ->from('Car:CarModification', 'cm')
            ->where("1 > 0 $str")
            ->orderBy('cm.year')
//            ->groupBy('cmodif.year')
            ->setParameters($pars);

        return $queryBuilder->getQuery()->getArrayResult();
    }



    /**
     * @param array $requestData
     * @return array
     */
    public function getYears($requestData)
    {
        $pars = array();
        $str = '';

        $pars['brandId'] = $this->getBrand()->getId();
        $str .= ' AND cb.id = :brandId';

        if (!empty($requestData['series'])) {
            $pars['series'] = $requestData['series'];
            $str .= ' AND cm.series_raw = :series';
        }

        if (!empty($requestData['model'])) {
            $pars['model'] = str_replace("-", " ", $requestData['model']);
            $str .= ' AND cm.name = :model';
        }

        if (!empty($requestData['region'])) {
            $pars['region'] = $requestData['region'];
            $str .= ' AND cm.region = :region';
        }

        if (!empty($requestData['body'])) {
            $pars['body'] = $requestData['body'];
            $str .= ' AND cm.body_raw = :body';
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cmodif.year')
            ->distinct()
            ->from('Car:CarBrand', 'cb')
            ->innerJoin('Car:CarModel','cm', Join::WITH, 'cm.carBrand = cb.id')
            ->innerJoin('Car:CarModification','cmodif', Join::WITH, 'cmodif.carModel = cm.id')
            ->where("1 > 0 $str")
            ->orderBy('cmodif.year')
//            ->groupBy('cmodif.year')
            ->setParameters($pars);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param array $requestData
     * @return array
     */
    public function getMonths($requestData)
    {
        $item = Array();
        $pars = Array();
        $str = '';

        if(isset($requestData['year']) && $requestData['year'] ) {
            $pars['year'] = $requestData['year'];
            $str .= ' AND cmodif.year = :year ';
        }

        if(isset($requestData['car_model_name']) && $requestData['car_model_name'] != 'N' ) {
            $pars['carModel'] = str_replace("-", " ", $requestData['car_model_name']);
            $str .= ' AND cm.name = :carModel ';
        }

        if(isset($requestData['steering']) && $requestData['steering'] && $requestData['steering'] != 'N' ) {
            $pars['steering'] = $requestData['steering'];
            $str .= ' AND cmodif.steering = :steering ';
        }

        if(isset($requestData['gearbox']) && $requestData['gearbox'] && $requestData['gearbox'] != 'N' ) {
            $pars['gearbox'] = $requestData['gearbox'];
            $str .= ' AND cmodif.gearbox = :gearbox ';
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cmodif.month')
            ->distinct()
            ->from('Car:CarModification', 'cmodif')
            ->innerJoin('Car:CarModel','cm', \Doctrine\ORM\Query\Expr\Join::WITH, 'cm.id = cmodif.carModel')
            ->where(" 1>0 $str")
            ->orderBy('cmodif.month')
//            ->groupBy('cmodif.month')
            ->setParameters($pars);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param $vin
     * @return CarModification|null
     */
    public function getCarModificationByVIN($vin)
    {
        $chassisNumber  = substr($vin, -7);
        $prefixNumber   = substr($vin, -7, 2);

        /** @var CarBmwNumber $carBmwNumber */
        $carBmwNumber = $this->entityManager
            ->createQueryBuilder()
                ->select('c')
                ->from('Car:CarBmwNumber', 'c')  //TODO: Сделать автоматическое определение бренда по VIN-номеру
                ->where('c.from <= :chassis')
                ->andWhere('c.to >= :chassis')
                ->andWhere('c.prefix = :prefix')
            ->setParameters(array(
                'chassis' => $chassisNumber,
                'prefix' => $prefixNumber,
            ))
            ->getQuery()
            ->getOneOrNullResult();

        if ($carBmwNumber) {
            $year = substr($carBmwNumber->getDate(), 0, 4);
            $month = substr($carBmwNumber->getDate(), 4, 2);

            $queryBuilder = $this
                ->entityManager
                ->createQueryBuilder()
                ->select('c')
                ->from('Car:CarModification', 'c')
                ->where('c.carModel = :carModelId')
                ->andWhere('c.year = :year')
                ->andWhere('c.month = :month')
                ->andWhere('c.steering = :steering')
                ->andWhere('c.gearbox = :gearbox')
                ->setParameters(array(
                    'carModelId' => $carBmwNumber->getCarModel()->getId(),
                    'year' => $year,
                    'month' => $month,
                    'steering' => $carBmwNumber->getSteering(),
                    'gearbox' => $carBmwNumber->getGearbox(),
                ));

            return $queryBuilder->getQuery()->getOneOrNullResult();
        }

        return null;
    }

    /**
     * @param $partNumber
     * @return CarPartsInfo
     */
    public function getPartsInfo($partNumber)
    {
        return $this->entityManager->getRepository('Car:CarPartsInfo')->find($partNumber);
    }

    /**
     * @param CarModel $carModel
     * @param CarUnit $carUnit
     * @return CarUnitOption[]
     */
    public function getUnitOptions(CarModel $carModel, CarUnit $carUnit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1')
            ->distinct()
            ->from('Car:CarOption', 'c1')
            ->innerJoin('c1.carUnitOption', 'c2')
            ->innerJoin('c2.carModel', 'c3')
            ->where('c3.id = :carModelId')
            ->andWhere('c2.car_unit_code = :carUnitCode')
            ->orderBy('c1.name')
            ->setParameters(array(
                'carModelId' => $carModel->getId(),
                'carUnitCode' => $carUnit->getCode(),
            ));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param CarUnit $carUnit
     * @return array
     */
    private function getUnitOptionRestrictions(CarUnit $carUnit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.available, c1.operator, c1.operator_info, c2.code, c1.position, c2.name')
            ->distinct()
            ->from('Car:CarUnitOption', 'c1')
            ->innerJoin('c1.carOption', 'c2')
            ->where('c1.car_unit_code = :carUnitCode')
            ->andWhere('c1.car_option_group_id = :carOptionGroupId')
            ->andWhere('c1.carModel IS NULL')
            ->orderBy('c1.car_option_group_id')->addOrderBy('c1.position')
            ->setParameters(array(
                'carOptionGroupId' => $carUnit->getCarOptionGroupId(),
                'carUnitCode' => $carUnit->getCode(),
            ));

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param $carParts CarRelationParts[]
     * @param CarUnit $carUnit
     * @return array
     */
    private function getPartsOptionRestrictions($carParts, CarUnit $carUnit)
    {
        $carOptionGroups = [];

        foreach ($carParts as $parts) {
            if (!in_array($parts->getCarOptionGroupId(), $carOptionGroups)) {
                $carOptionGroups[] = $parts->getCarOptionGroupId();
            }
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('c1.car_option_group_id, c1.available, c1.operator, c1.operator_info, c2.code, c1.position, c2.name')
            ->distinct()
            ->from('Car:CarUnitOption', 'c1')
            ->innerJoin('c1.carOption', 'c2')
            ->where('c1.car_unit_code = :carUnitCode')
            ->andWhere('c1.car_option_group_id IN (:carOptionGroups)')
            ->andWhere('c1.carModel = :carModel')
            ->orderBy('c1.car_option_group_id')->addOrderBy('c1.position')
            ->setParameters(array(
                'carOptionGroups' => $carOptionGroups,
                'carUnitCode' => $carUnit->getCode(),
                'carModel' => $carUnit->getCarModel()
            ));

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param $restrictions array
     * @return array
     */
    private function getGroupedRestrictions($restrictions)
    {
        $grouped = [];
        $names = [];

        foreach ($restrictions as $r) {
            $id = empty($r['car_option_group_id'])? 'ALL': $r['car_option_group_id'];
            $code = $r['code'];
            $info = $r['operator_info'];
            $operator = $r['operator'];
            $position = $r['position'];
            $available = $r['available'] ? '+' : '-';
            $names[$code] = $r['name'];
            switch ($operator) {
                case 'E':
                case 'B':
                    $grouped[$id]["BIN_INFO"] = $info;
                    $grouped[$id]['BIN'][$position] = $code;
                    break;
                case 'U':
                    $grouped[$id]['&'][0][] = $available . $code;
                    break;
                case 'O':
                    $grouped[$id]['|'][0][] = $available . $code;
                    break;
            }
        }

        foreach ($grouped as &$group) {
            if (isset($group['BIN'])) {
                $i = 0;
                while (($pos = strpos($group['BIN_INFO'], 'X')) !== false) {
                    $mask = strlen($group['BIN_INFO']) - $pos - 1;
                    foreach ($group['BIN'] as $position => $option) {
                        if ($mask & pow(2, abs($position - count($group['BIN'])))) {
                            $group['&'][$i][] = '+' . $option;
                        } else {
                            $group['&'][$i][] = '-' . $option;
                        }
                    }
                    $group['BIN_INFO'][$pos] = '0';
                    $i++;
                }
                unset($group['BIN_INFO']);
                unset($group['BIN']);
            }
        }

        return array(
            'groups' => $grouped,
            'labels' => $names
        );
    }

    /**
     * @param $carUnit CarUnit
     * @param $carParts CarRelationParts[]
     * @return array
     */
    public function getOptionRestrictions(CarUnit $carUnit, $carParts)
    {
        $unitRestrictions = $this->getUnitOptionRestrictions($carUnit);
        $partsRestrictions = $this->getPartsOptionRestrictions($carParts, $carUnit);
        $gUnitRestrictions = $this->getGroupedRestrictions($unitRestrictions);
        $gPartsRestrictions = $this->getGroupedRestrictions($partsRestrictions);

        return array(
            'groups' => array_merge($gUnitRestrictions['groups'], $gPartsRestrictions['groups']),
            'labels' => array_merge($gUnitRestrictions['labels'], $gPartsRestrictions['labels'])
        );
    }

    /**
     * @param $carParts CarRelationParts[]
     * @return array
     */
    public function removeDuplicateParts($carParts)
    {
        $grouped = [];

        foreach ($carParts as $parts) {
            $hash = md5(
                $parts->getCarParts()->getId().
                $parts->getImageNumber().
                $parts->getQuantity().
                $parts->getComment()
            );
            $groupId = $parts->getCarOptionGroupId();
            $grouped[$hash]['data'] = $parts;
            if (empty($grouped[$hash]['groups']) || !in_array($groupId, $grouped[$hash]['groups'])) {
                $grouped[$hash]['groups'][] = $groupId;
            }
        }

        return $grouped;
    }
}
