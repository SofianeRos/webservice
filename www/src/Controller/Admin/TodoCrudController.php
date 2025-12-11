<?php

namespace App\Controller\Admin;

use App\Entity\Todo;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class TodoCrudController extends AbstractCrudController
{
    //! syntaxe php7
    // private Security $security;

    // public function __construct(Security $security)
    // {
    //     $this->security = $security;
    // }

    //! syntaxe php 8 
    public function __construct(private Security $security)
    {
    }


    
    public static function getEntityFqcn(): string
    {
        return Todo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("tache")
            ->setEntityLabelInPlural("tache")
            ->setPageTitle('index', 'Liste des taches')
            ->setPageTitle('new', 'Créer une tache')
            ->setPageTitle('edit', 'Modifier la tache')
            ->setPageTitle('detail', 'Détails de la tache')
            ->setSearchFields(['title', 'description'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $actions) {
                return $actions
                    ->setLabel('Ajouter nouveau')
                    ->setIcon('fa fa-plus')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $actions) {
                return $actions
                    ->setLabel('Modifier')
                    ->setIcon('fa fa-pen')
                    ->setCssClass('btn btn-warning');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $actions) {
                return $actions
                    ->setLabel('Supprimer')
                    ->setIcon('fa fa-trash')
                    ->setCssClass('btn btn-danger');
            })
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $actions) {
                return $actions
                    ->setLabel('Voir details')
                    ->setIcon('fa fa-eye')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $actions) {
                return $actions
                    ->setLabel('Enregistrer et ajouter un nouveau');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $actions) {
                return $actions
                    ->setLabel('Enregistrer et quitter');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $actions) {
                return $actions
                    ->setLabel('Mettre à jour et continuer');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $actions) {
                return $actions
                    ->setLabel('Mettre à jour et quitter');
            })
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(Crud::PAGE_EDIT, Action::INDEX, function (Action $actions) {
                return $actions
                    ->setLabel('Retour à l\'accueil');
            })
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_NEW, Action::INDEX, function (Action $actions) {
                return $actions
                    ->setLabel('Retour à l\'accueil');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $actions) {
                return $actions
                    ->setLabel('Supprimer')
                    ->setIcon('fa fa-trash')
                    ->setCssClass('btn btn-danger');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $actions) {
                return $actions
                    ->setLabel('Retour à l\'accueil');
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $actions) {
                return $actions
                    ->setLabel('Modifier');
            })
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Todo) {
            $entityInstance->setCreatedAt(new \DateTime());
        };
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Todo) {
            $entityInstance->setUpdatedAt(new \DateTime());
        };
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield TextField::new('title', 'Titre')
            ->setRequired(true)
            ->setHelp('donner un titre claire et concis à votre tache');
        yield TextareaField::new('description', 'Description')
            ->setHelp('Décrire en détail la tache à accomplir');
        yield BooleanField::new('isCompleted', 'Terminée')
            ->setHelp('Cocher si la tache est terminée');
        yield BooleanField::new('isUrgent', 'Urgente')
            ->setHelp('Cocher si la tache est urgente');
        yield ImageField::new('mediaPath', 'Image/Média')
            ->setBasePath('/upload/todos/')
            ->setUploadDir('public/upload/todos/')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setHelp('Format accepter: jpg, jpeg, png, webp, avif, gif. taille max: 5Mo')
            ->setFileConstraints(
                new Image(
                    maxSize: '5M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'],
                    mimeTypesMessage: 'Veuillez uploader une image valide (jpg, jpeg, png, webp, avif, gif).'
                )


            );
        yield AssociationField::new('user', 'Utilisateur')
            ->hideOnForm()
            ->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Crée le:')
            ->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Modifier le:')
            ->hideOnForm();

        yield DateTimeField::new('deadline', 'Date limite:')
            ->setHelp('La date limite pour accomplir la tâche');
    }

    public function createEntity(string $entityFqcn)
    {
        $todo = new Todo();
        $todo->setUser($this->security->getUser());
        return $todo;
    }
}
