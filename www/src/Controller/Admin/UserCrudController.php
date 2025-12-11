<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher){}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Utilisateur")
            ->setEntityLabelInPlural("Utilisateurs")
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('new', 'Créer un utilisateur')
            ->setPageTitle('edit', 'Modifier l\'utilisateur')
            ->setPageTitle('detail', 'Détails de l\'utilisateur')
            ->setSearchFields(['email', 'firstname', 'lastname'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }


    public function configureFields(string $pageName): iterable
    {
            yield IdField::new('id', 'ID')
            ->hideOnForm();

            yield EmailField::new('email', 'Email')
            ->setRequired(true);

            yield TextField::new('firstname', 'Prénom')
            ->setRequired(true);

            yield TextField::new('lastname', 'Nom')
            ->setRequired(true);

            yield TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp("Le mot de passe doit contenir 1 majuscule, 1 minuscule, 1 chiffre et 8 caractères minimum")
            ->setFormTypeOptions([
                'mapped' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', 'Le mot de passe doit contenir 1 majuscule, 1 minuscule, 1 chiffre et 8 caractères minimum')
                ],
            ]);

            yield ArrayField::new('roles', 'Rôles');

            yield BooleanField::new('isActive', 'Actif');

            yield DateTimeField::new('createdAt', 'Crée le:')
            ->hideOnForm();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if($entityInstance instanceof User && !$entityInstance->getCreatedAt()){
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setCreatedAt(new \DateTime());
            $entityInstance->setPassword($hashedPassword);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            // Récupération de la valeur du champ password depuis la requête (non mappé)
            $request = $this->getContext()->getRequest();
            $formData = $request->request->all();
            
            // Recherche du champ password dans les données du formulaire
            $plainPassword = null;
            foreach ($formData as $key => $value) {
                if (is_array($value) && isset($value['password'])) {
                    $plainPassword = $value['password'];
                    break;
                }
            }

            // Si le champ password est vide ou null, on garde le mot de passe original
            if ($plainPassword === null || $plainPassword === '') {
                // Récupération du mot de passe original depuis la base de données
                $originalEntity = $entityManager->getRepository(User::class)->find($entityInstance->getId());
                if ($originalEntity && $originalEntity->getPassword() !== null) {
                    $entityInstance->setPassword($originalEntity->getPassword());
                }
            } else {
                // Si un nouveau mot de passe est fourni, on le hash
                $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $plainPassword);
                $entityInstance->setPassword($hashedPassword);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
 
    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function(Action $action){
            return $action
            ->setLabel('Ajouter nouveau')
            ->setIcon('fa fa-user-plus')
            ->setCssClass('btn btn-success')
            ;
        })

        ->update(Crud::PAGE_INDEX, Action::EDIT, function(Action $action){
            return $action
            ->setLabel('Modifier')
            ->setIcon('fa fa-pen')
            ->setCssClass('btn btn-warning')
            ;
        })

        ->update(Crud::PAGE_INDEX, Action::DELETE, function(Action $action){
            return $action
            ->setLabel('Supprimer')
            ->setIcon('fa fa-trash')
            ->setCssClass('btn btn-danger')
            ;
        })

        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->update(Crud::PAGE_INDEX, Action::DETAIL, function(Action $action){
            return $action
            ->setLabel('Voir detail')
            ->setIcon('fa fa-eye')
            ->setCssClass('btn btn-success')
            ;
        })

        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function(Action $action){
            return $action
            ->setLabel('Enregistrer et ajouté un nouveau')
            ;
        })

        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function(Action $action){
            return $action
            ->setLabel('Enregistrer et quitter')
            ;
        })

        ->add(Crud::PAGE_NEW, Action::INDEX)
        ->update(Crud::PAGE_NEW, Action::INDEX, function(Action $action){
            return $action
            ->setLabel('Retour à l\'acceuil')
            ;
        })

        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function(Action $action){
            return $action
            ->setLabel('Mettre à jour et continuer de modifié')
            ;
        })

        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function(Action $action){
            return $action
            ->setLabel('Mettre à jour et quitter')
            ;
        })

        ->add(Crud::PAGE_EDIT, Action::INDEX)
        ->update(Crud::PAGE_EDIT, Action::INDEX, function(Action $action){
            return $action
            ->setLabel('Retour à l\'acceuil')
            ;
        })

        ->update(Crud::PAGE_DETAIL, Action::EDIT, function(Action $action){
            return $action
            ->setLabel('Modifier')
            ;
        })

        ->update(Crud::PAGE_DETAIL, Action::INDEX, function(Action $action){
            return $action
            ->setLabel('Retour à l\'acceuil')
            ;
        })

        ->update(Crud::PAGE_DETAIL, Action::DELETE, function(Action $action){
            return $action
            ->setLabel('Supprimer')
            ;
        })

        ;
    }
}