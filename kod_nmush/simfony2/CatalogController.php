<?php
/**
 * Created by Armen Ishkhanyan.
 * Email: armjer@mail.ru
 * Date: 14.08.15 14:47
 */

namespace AppBundle\Controller;

use AppBundle\Entity\CarModification;
use AppBundle\Services\CarService;
use AppBundle\Services\ImageLocator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Task;
use AppBundle\Form\Type\SearchType;
use AppBundle\Form\Type\SearchTypeDetails;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CatalogController
 * @package AppBundle\Controller
 */
class CatalogController extends Controller
{
    private $formSearchFilter;
    private $historyArr;
    /**
     * Override method to call #containerInitialized method when container set.
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->containerInitialized();
    }

    /**
     * Perform some operations after controller initialized and container set.
     */
    private function containerInitialized()
    {
        // some tasks to do...
        $request = new Request();
        $searchCascade = new Task();
        $this->formSearchFilter = $this->createForm(new SearchType(), $searchCascade);

        if ($request->isMethod('POST')) {
            $this->formSearchFilter->bind($request);

            if ($this->formSearchFilter->isValid()) {
                // валидация прошла успешно, можно выполнять дальнейшие действия с объектом
                //$this->redirect($this->generateUrl('...'));
            }
        }

        $response = new Response();
        $cookies = $response->headers->getCookies();
        $cookies = $request->cookies;

        $historyArr = Array();
        $historyUrl = '/parts-bmw/';
        foreach($cookies  as $k => $v) {
            $r = preg_match('/^history_/ius', $k);
            if($r ) {
                $cArr = explode('chukodelim', $v);
                $cArr[0] = preg_replace('/BMW!@@!#/ius', '', $cArr[0]);
                $sUrl = preg_replace('/!@@!#/ius', ':',  $cArr[0]);
                $sUrl = preg_replace('/:\//ius', '/',  $sUrl);

                $sText = preg_replace('/!@@!#/ius', ' ',  @$cArr[1]);

                $historyArr[$k]['url'] = 'parts-bmw/'.$sUrl;
                $historyArr[$k]['text'] = $sText;
                $historyArr[$k]['cookieName'] = $k;
            }
        }
        $this->historyArr = $historyArr;

    }


    /**
     * Просмотр всех категорий запчастей для выбранного автомобиля
     *
     * Для проверки: /parts-bmw/530i-M54:E60:EGY:Lim/L:N:2004:12/
     *
     * @Route(
     *    "/parts-{brand}/{model}:{series}:{region}:{body}/{steering}:{gearbox}:{year}:{month}/"
     * )
     * @param $brand string
     * @param $series string
     * @param $model string
     * @param $region string
     * @param $body string
     * @param $request Request
     * @return Response
     */
    public function catalogAction(
        $brand,
        $series,
        $model,
        $region,
        $body,
        Request $request
    ) {
        /** @var CarService $carService */
        $carService = $this->get('car_service');

        $carModel       = $carService->getModelByParams($series, $model, $region, $body);
        $carPartsGroups = $carService->getPartsGroups($carModel);

        return $this->render(
            'AppBundle:Index:catalog.html.twig',
            array(
                'carModel' => $carModel,
                'carPartsGroups' => $carPartsGroups,
                'series' => $series,
                'brandName' => $brand,
                'uri' => $request->getPathInfo(),
                //'sarchcar' => true,
                'formSearchFilter' => $this->formSearchFilter->createView(),
                'historyArr' => $this->historyArr
            )
        );
    }

    /**
     * Просмотр выбранного узла и запчастей
     *
     * Для проверки: /parts-bmw/530i-M54:E60:EGY:Lim/L:N:2004:12/11/11_2180/
     *
     * @Route(
     *    "/parts-{brand}/{model}:{series}:{region}:{body}/{steering}:{gearbox}:{year}:{month}/{partsGroup}/{unitCode}/"
     * )
     * @param $brand string
     * @param $series string
     * @param $model string
     * @param $region string
     * @param $body string
     * @param $steering string
     * @param $gearbox string
     * @param $year int|string
     * @param $month int|string
     * @param $unitCode string
     * @param $partsGroup string
     * @param $request Request
     * @return Response
     */
    public function catalogItemAction(
        $brand,
        $series,
        $model,
        $region,
        $body,
        $steering,
        $gearbox,
        $year,
        $month,
        $partsGroup,
        $unitCode,
        Request $request
    ) {
        $partsGroupId = $this->parseGroupId($partsGroup);
        $unitCode = $this->parseGroupId($unitCode);

        /** @var CarService $carService */
        $carService = $this->get('car_service');

        $carModel       = $carService->getModelByParams($series, $model, $region, $body);
        $carPartsGroup  = $carService->getPartsGroupByParam($carModel, $partsGroupId);
        $carUnit        = $carService->getUnitByParams($carModel, $steering, $gearbox, $year, $month, $unitCode);
        $carParts       = $carService->getPartsByParams($carModel, $carUnit);
        $carImageMap    = $carService->getCarImageMap($carUnit->getImageId(), $carParts);
        $carUnitOptions = $carService->getUnitOptions($carModel, $carUnit);
        $restrictions   = $carService->getOptionRestrictions($carUnit, $carParts);
        $cleanedParts   = $carService->removeDuplicateParts($carParts);

        return $this->render(
            'AppBundle:Index:catalogitem.html.twig',
            array(
                'carModel' => $carModel,
                'carUnit' => $carUnit,
                'carParts' => $cleanedParts,
                'carImageMap' => $carImageMap,
                'carPartsGroup' => $carPartsGroup,
                'carUnitOptions' => $carUnitOptions,
                'uri' => $request->getPathInfo(),
                'brandName' => $brand,
                'restrictions' => $restrictions,
                'hidesubScribeBlock' => true
            )
        );
    }

    /**
     * Просмотр узлов запчастей автомобиля для выбранной категории
     *
     * Для проверки: /parts-bmw/530i-M54:E60:EGY:Lim/L:N:2004:12/11/
     *
     * @Route("/parts-{brand}/{model}:{series}:{region}:{body}/{steering}:{gearbox}:{year}:{month}/{partsGroup}/")
     * @param $brand string
     * @param $series string
     * @param $model string
     * @param $region string
     * @param $body string
     * @param $steering string
     * @param $gearbox string
     * @param $year int|string
     * @param $month int|string
     * @param $partsGroup string
     * @param $request Request
     * @return Response
     */
    public function categoryItemAction(
        $brand,
        $series,
        $model,
        $region,
        $body,
        $steering,
        $gearbox,
        $year,
        $month,
        $partsGroup,
        Request $request
    ) {
        $partsGroupId = $this->parseGroupId($partsGroup);

        /** @var CarService $carService */
        $carService = $this->get('car_service');

        $carModel       = $carService->getModelByParams($series, $model, $region, $body);
        $carPartsGroup  = $carService->getPartsGroupByParam($carModel, $partsGroupId);
        $carUnits       = $carService->getUnitsByParams($carModel, $steering, $gearbox, $year, $month, $partsGroupId);

        return $this->render(
            'AppBundle:Index:categoryitem.html.twig',
            array(
                'carPartsGroup' => $carPartsGroup,
                'carUnits' => $carUnits,
                'brandName' => $brand,
                'carModel' => $carModel,
                'uri' => $request->getPathInfo(),
            )
        );
    }

    /**
     * Загрузка изображения, временное решение
     *
     * Для проверки: /parts-bmw/image/n/61864/
     *
     *      n - изображение обычного размера
     *      s - изображение уменьшенного размера
     *
     * @Route("/parts-{brand}/image/{type}/{imageId}/")
     * @param $brand string
     * @param $type string
     * @param $imageId int
     * @return Response
     */
    public function imageAction($brand, $type, $imageId)
    {
        /** @var ImageLocator $imageLocator */
        $imageLocator = $this->get('image_locator');
        $partsImage = $imageLocator->get(strtolower($brand), $imageId, $type);
        if ($partsImage === null) {
            throw $this->createNotFoundException('Image is not found');
        } else {
            return $this->redirect($partsImage->getWebPath());
        }
    }

    /**
     * Поиск автомобиля по VIN-номеру
     *
     * Для проверки: /parts/vin/WBAFB71080LV50974/
     *
     * @Route("/parts/vin/{vin}/")
     * @param $vin string
     * @return Response
     */
    public function vinRequestAction($vin)
    {
        /** @var CarService $carService */
        $carService = $this->get('car_service');
        $carService->setBrandName('BMW'); //TODO: Сделать автоматическое определение бренда по VIN-номеру

        $carModification = $carService->getCarModificationByVIN($vin);
        if ($carModification === null) {
            throw $this->createNotFoundException('Car modification is not found');
        } else {
            return $this->goToModification($carModification);
        }
    }

    /**
     * @param CarModification $carModification
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function goToModification(CarModification $carModification)
    {
        $brand      = strtolower($carModification->getCarModel()->getCarBrand()->getName());
        $series     = $carModification->getCarModel()->getSeriesRaw();
        $model      = str_replace(" ", "-", $carModification->getCarModel()->getName());
        $region     = $carModification->getCarModel()->getRegion();
        $body       = $carModification->getCarModel()->getBodyRaw();
        $steering   = $carModification->getSteering();
        $gearbox    = $carModification->getGearbox();
        $year       = $carModification->getYear();
        $month      = $carModification->getMonth();

        return $this->redirect("/parts-$brand/$series:$model:$region:$body/$steering:$gearbox:$year:$month/");
    }

    /**
     * Карточка запчасти
     *
     * Для проверки: /parts-bmw/220d-N47N:F22:ECE:Cou/L:N:2012:10/2/02_0013/34116792219/
     *
     * @Route(
     *    "/parts-{brand}/{model}:{series}:{region}:{body}/{steering}:{gearbox}:{year}:{month}/{partsGroup}/{unitCode}/{partNumber}/"
     * )
     * @param $brand string
     * @param $series string
     * @param $model string
     * @param $region string
     * @param $body string
     * @param $steering string
     * @param $gearbox string
     * @param $year int|string
     * @param $month int|string
     * @param $partsGroup string
     * @param $unitCode string
     * @param $partNumber string
     * @param $request Request
     * @return Response
     */
    public function partsInfoAction(
        $brand,
        $series,
        $model,
        $region,
        $body,
        $steering,
        $gearbox,
        $year,
        $month,
        $partsGroup,
        $unitCode,
        $partNumber,
        Request $request
    ) {
        $imageNumber = $request->get('i');
        $partsGroupId = $this->parseGroupId($partsGroup);
        $unitCode = $this->parseGroupId($unitCode);

        /** @var CarService $carService */
        $carService = $this->get('car_service');

        $carPartsInfo   = $carService->getPartsInfo($partNumber);
        $carModel       = $carService->getModelByParams($series, $model, $region, $body);
        $carPartsGroup  = $carService->getPartsGroupByParam($carModel, $partsGroupId);
        $carUnit        = $carService->getUnitByParams($carModel, $steering, $gearbox, $year, $month, $unitCode);
        $carParts       = $carService->getPartsByParams($carModel, $carUnit);
        $carImageMap    = $carService->getCarImageMap($carUnit->getImageId(), $carParts);

        return $this->render(
            'AppBundle:Index:productpage.html.twig',
            array(
                'carPartsInfo' => $carPartsInfo,
                'carUnit' => $carUnit,
                'carImageMap' => $carImageMap,
                'carPartsGroup' => $carPartsGroup,
                'carModel' => $carModel,
                'brandName' => $brand,
                'imageNumber' => $imageNumber,
                'uri' => $request->getPathInfo(),
            )
        );
    }

    /**
     * @param $group
     * @return null|string
     */
    private function parseGroupId($group)
    {
        if (preg_match("/([0-9\\_]*)$/", $group, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
