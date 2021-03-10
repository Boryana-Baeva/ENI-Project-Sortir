<?php

namespace App\Controller\Admin;

use App\Entity\Outing;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OutingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Outing::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            DateTimeField::new('startDateTime'),
            NumberField::new('duration'),
            DateTimeField::new('entryDeadline'),
            NumberField::new('maxNumberEntries'),
            TextareaField::new('description'),
            TextField::new('state'),

        ];
    }

    public function configureActions(Actions $actions): Actions
    {

        $cancel = Action::new('cancel', 'annuler')
            ->linkToRoute('outing_cancel', ['id'=>$this->getParameter('id')]);
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX,$cancel);
    }

}
