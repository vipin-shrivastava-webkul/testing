<?php

namespace UVDesk\CommunityPackages\UVDesk\FormComponent\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="uv_pkg_uvdesk_form_component_updatedform")
 */
class UpdatedForm
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * 
     */
    protected $formId;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $fieldId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;
    
    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $placeholder;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set formId
     *
     * @param integer $formId
     * @return UpdatedForm
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * Get formId
     *
     * @return integer
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Get fieldId
     *
     * @return integer
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set fieldId
     *
     * @param integer $fieldId
     * @return UpdatedForm
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return UpdatedForm
     */
    public function setFieldName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getFieldName()
    {
        return $this->name; 
    }

    /**
     * Set placeholder
     *
     * @param string $placeholder
     * @return UpdatedForm
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder
     *
     * @return string 
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }
}