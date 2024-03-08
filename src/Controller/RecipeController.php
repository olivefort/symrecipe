<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{

    //[cRud] Lecture d'une recette uniquement pour les utilisateurs enregistré 
    #[IsGranted('ROLE_USER')]
    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    public function index(
        RecipeRepository $repository, 
        PaginatorInterface $paginator, 
        Request $request
    ):Response{
            $recipes = $paginator->paginate(
                $repository->findBy(['user' => $this->getUser()]
                // ,['createdAt'=>'DESC']
            ),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    //[cRud] Lecture d'une recette uniquement pour tout le monde 
    #[Route('/recette/publique', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(
        PaginatorInterface $paginator,
        RecipeRepository $repository,
        Request $request
    ):Response{
        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request->query->getInt('page', 1),
            16
        );
        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }


    //[Crud] Création d'une recette
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/creation', name: 'recipe.new', methods: ['GET','POST'])]
    public function new(
        EntityManagerInterface $manager,
        Request $request
    ) :Response {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe); 

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette à bien été ajouté avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }
        
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }



    
     //[crUd] Edition d'une recette
     #[IsGranted(
        new Expression("is_granted('ROLE_USER') and user === subject.getUser()"),
        subject:'recipe',
    )]
    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ['GET','POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $manager,
        Recipe $recipe
    ) : Response {

        $form = $this->createForm(RecipeType::class,$recipe);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();
            
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifié avec succès!'
            );

            return $this->redirectToRoute('recipe.index');
        }
        

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }


    //[cruD] Suppression d'une recette
    #[IsGranted(
        new Expression("is_granted('ROLE_USER') and user === subject.getUser()"),
        subject:'recipe',
    )]
    #[Route('recette/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $manager,
        Recipe $recipe
    ):Response{
        if ($recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();        
        }else{
            $manager->remove($recipe);
            $manager->flush();
        }

        $this->addFlash(
            'success',
            'Votre recette a été supprimé avec succès !'
        );

        return $this->redirectToRoute('recipe.index');
    }

    //Page de la recette réservé au utilisateurs
    #[IsGranted(
        new Expression("is_granted('ROLE_USER') and subject.isIsPublic() === true"),
        subject:'recipe',
    )]
    #[Route('recette/{id}', 'recipe.show', methods: ['GET','POST'])]
    public function show(
        Recipe $recipe,
        Request $request,
        MarkRepository $markRepository,
        EntityManagerInterface $manager
    ):Response{
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);
            $existingMark = $markRepository->findOneBy([
                'user'      => $this->getUser(),
                'recipe'    => $recipe
            ]);
            if(!$existingMark){
                $manager->persist($mark);
            }else{
                $existingMark -> setMark(
                    $form->getData()->getMark()
                );
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Votre note à bien été prise en compte.'
            );
            return $this->redirectToRoute('recipe.show', [
                'id' => $recipe->getId()
            ]);
        }
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
        
    }    
}
