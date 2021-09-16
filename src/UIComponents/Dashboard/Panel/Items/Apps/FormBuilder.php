<?php

namespace UVDesk\CommunityPackages\UVDesk\FormComponent\UIComponents\Dashboard\Panel\Items\Apps;

use Webkul\UVDesk\CoreFrameworkBundle\Dashboard\Segments\PanelSidebarItemInterface;
use Webkul\UVDesk\ExtensionFrameworkBundle\UIComponents\Dashboard\Homepage\Sections\Apps;

class FormBuilder implements PanelSidebarItemInterface
{
    public static function getTitle() : string
    {
        return "Form Builder";
    }

    public static function getRouteName() : string
    {
        return 'uvdesk_form_component_form_builder';
    }

    public static function getSupportedRoutes() : array
    {
        return [
            'uvdesk_form_component_form_builder',
            'uvdesk_form_component_form_builder_xhraction',
            'uvdesk_form_component_form_builder_reorder_action',
        ];
    }

    public static function getRoles() : array
    {
        return ['ROLE_ADMIN'];
    }

    public static function getSidebarReferenceId() : string
    {
        return Apps::class;
    }
}