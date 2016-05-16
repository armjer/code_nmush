<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="brand_series_idx", columns={"car_brand_id","series_raw"})})
 */
class CarModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $series;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $series_raw;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $body_raw;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity="CarPartsGroup", mappedBy="carModel")
     */
    private $carPartsGroup;

    /**
     * @ORM\OneToMany(targetEntity="CarUnit", mappedBy="carModel")
     */
    private $carUnit;

    /**
     * @ORM\OneToMany(targetEntity="CarUnitOption", mappedBy="carModel")
     */
    private $carUnitOption;

    /**
     * @ORM\OneToMany(targetEntity="CarModification", mappedBy="carModel")
     */
    private $carModificationGroup;

    /**
     * @ORM\OneToMany(targetEntity="CarRelationParts", mappedBy="carModel")
     */
    private $carRelationParts;

    /**
     * @ORM\ManyToOne(targetEntity="CarBrand", inversedBy="carModel")
     * @ORM\JoinColumn(name="car_brand_id", referencedColumnName="id", nullable=false)
     */
    private $carBrand;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $image_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeriesRaw()
    {
        return $this->series_raw;
    }

    /**
     * @param mixed $series_raw
     * @return self
     */
    public function setSeriesRaw($series_raw)
    {
        $this->series_raw = $series_raw;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param mixed $series
     * @return self
     */
    public function setSeries($series)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     * @return self
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCarModificationGroup()
    {
        return $this->carModificationGroup;
    }

    /**
     * @param mixed $carModificationGroup
     * @return self
     */
    public function setCarModificationGroup($carModificationGroup)
    {
        $this->carModificationGroup = $carModificationGroup;

        return $this;
    }

    /**
     * @return CarBrand|null
     */
    public function getCarBrand()
    {
        return $this->carBrand;
    }

    /**
     * @param mixed $carBrand
     * @return self
     */
    public function setCarBrand($carBrand)
    {
        $this->carBrand = $carBrand;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBodyRaw()
    {
        return $this->body_raw;
    }

    /**
     * @param mixed $body_raw
     * @return self
     */
    public function setBodyRaw($body_raw)
    {
        $this->body_raw = $body_raw;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCarPartsGroup()
    {
        return $this->carPartsGroup;
    }

    /**
     * @param mixed $carPartsGroup
     * @return self
     */
    public function setCarPartsGroup($carPartsGroup)
    {
        $this->carPartsGroup = $carPartsGroup;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCarUnit()
    {
        return $this->carUnit;
    }

    /**
     * @param mixed $carUnit
     * @return self
     */
    public function setCarUnit($carUnit)
    {
        $this->carUnit = $carUnit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCarUnitOption()
    {
        return $this->carUnitOption;
    }

    /**
     * @param mixed $carUnitOption
     * @return self
     */
    public function setCarUnitOption($carUnitOption)
    {
        $this->carUnitOption = $carUnitOption;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCarRelationParts()
    {
        return $this->carRelationParts;
    }

    /**
     * @param mixed $carRelationParts
     * @return self
     */
    public function setCarRelationParts($carRelationParts)
    {
        $this->carRelationParts = $carRelationParts;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageId()
    {
        return $this->image_id;
    }

    /**
     * @param mixed $image_id
     * @return self
     */
    public function setImageId($image_id)
    {
        $this->image_id = $image_id;

        return $this;
    }
}