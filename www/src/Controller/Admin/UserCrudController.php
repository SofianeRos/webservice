<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Email;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud 
        ->setEntityLabelInSingular("Utilisateur")
        ->setEntityLabelInPlural("Utilisateurs")
        ->setPageTitle('index','Liste des utilisateurs')
        ->setPageTitle('new','Ajouter un utilisateur')
        ->setPageTitle('edit','Modifier un utilisateur')
        ->setPageTitle('detail','Détails de l\'utilisateur')
        ->setSearchFields(['email','firstname','lastname'])
        ->setDefaultSort(['createdAt'=>'DESC']);
    }

    
    public function configureFields(string $pageName): iterable
    {
        
           yield IdField::new('id','ID')->hideOnForm();

           yield EmailField::new('email','Email')
           ->setRequired(true);

           yield TextField::new('firstname','Prénom')
           ->setRequired(true);

           yield TextField::new('lastname','Nom')
           ->setRequired(true);

           yield TextField::new('password','Mot de passe')
           ->setFormType(PasswordType::class)
           ->onlyOnForms()
           ->setRequired($pageName === Crud::PAGE_NEW)
           -> setHelp("Le mot de passe doit contenir 1 majuscule, 1 minuscule, 1 chiffre , 1 caractère spécial et 8 caractères minimum.")
           -> setFormTypeOptions([
               'constraints' => [
                  new \Symfony\Component\Validator\Constraints\Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/','Le mot de passe doit contenir 1 majuscule, 1 minuscule, 1 chiffre , 1 caractère spécial et 8 caractères minimum.')
                ]
              ]);
                     

           yield ArrayField::new('roles','Rôles');

           yield BooleanField::new('isActive','Actif');

           yield DateTimeField::new('createdAt','Créé le :')
           ->hideOnForm();
           
       
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && !$entityInstance->getCreatedAt()) {
            
            $entityInstance->setCreatedAt(new \DateTime());

        }
        parent::persistEntity($entityManager, $entityInstance);
    }
   public function configureActions(Actions $actions): Actions
   {
    return $actions
    ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
        return $action
        ->setLabel('Ajouter Nouveau Utilisateur')
        ->setIcon('fa fa-user-plus')
        ->setCssClass('btn btn-success')
        
        ;
    })
        ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
        return $action
        ->setLabel('Ajouter Nouveau')
        ->setIcon('fa fa-pencil-alt')
        ->setCssClass('btn btn-warning')
        
        ;
    })
    ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
        return $action
        ->setLabel('Supprimer')
        ->setIcon('fa fa-trash')
        ->setCssClass('btn btn-danger')

        
        
        ;
    })
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
        return $action
        ->setLabel('Détails')
        ->setIcon('fa fa-eye')
        ->setCssClass('btn btn-primary')
        
        ;
    })
    
    
    ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
        return $action
        ->setLabel('Enregistrer et Ajouter un Nouveau Utilisateur')
        
        
        ;
    })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
        return $action
        ->setLabel('Enregistrer et Quitter')
        
        
        ;

    })
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
        return $action
        ->setLabel('Mettre a jour et Continuer ')
        
        
        ;
    })
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
        return $action
        ->setLabel('Mettre a jour et Quitter ')
        
        
        ;
    })

    ->add(Crud::PAGE_EDIT, Action::INDEX , function (Action $action) {
        return $action
        ->setLabel('Retour à l\'acceuil')
        
        
        
        ;
    })
    
    ;


   }
}
