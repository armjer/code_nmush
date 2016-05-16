<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="image_id_idx", columns={"image_id"})})
 */
class CarImageMap
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $image_id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $image_number;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $top_left_x;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $top_left_y;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bottom_right_x;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bottom_right_y;

    /**
     * @var CarRelationParts Связь с запчастью
     */
    private $carRelationParts;

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

    /**
     * @return mixed
     */
    public function getTopLeftX()
    {
        return $this->top_left_x;
    }

    /**
     * @param mixed $top_left_x
     * @return self
     */
    public function setTopLeftX($top_left_x)
    {
        $this->top_left_x = $top_left_x;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTopLeftY()
    {
        return $this->top_left_y;
    }

    /**
     * @param mixed $top_left_y
     * @return self
     */
    public function setTopLeftY($top_left_y)
    {
        $this->top_left_y = $top_left_y;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBottomRightX()
    {
        return $this->bottom_right_x;
    }

    /**
     * @param mixed $bottom_right_x
     * @return self
     */
    public function setBottomRightX($bottom_right_x)
    {
        $this->bottom_right_x = $bottom_right_x;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBottomRightY()
    {
        return $this->bottom_right_y;
    }

    /**
     * @param mixed $bottom_right_y
     * @return self
     */
    public function setBottomRightY($bottom_right_y)
    {
        $this->bottom_right_y = $bottom_right_y;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageNumber()
    {
        return $this->image_number;
    }

    /**
     * @param mixed $image_number
     * @return self
     */
    public function setImageNumber($image_number)
    {
        $this->image_number = $image_number;

        return $this;
    }

    /**
     * @return CarRelationParts
     */
    public function getCarRelationParts()
    {
        return $this->carRelationParts;
    }

    /**
     * @param CarRelationParts $carRelationParts
     * @return self
     */
    public function setCarRelationParts(CarRelationParts $carRelationParts)
    {
        $this->carRelationParts = $carRelationParts;

        return $this;
    }
}