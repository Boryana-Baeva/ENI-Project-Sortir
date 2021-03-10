<?php

namespace App\Controller\Admin;

use App\Entity\Outing;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OutingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Outing::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
