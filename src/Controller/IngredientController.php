<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IngredientController extends AbstractController
{

    /**
     * This controller display all ingredients
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient.index', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        IngredientRepository $repository, 
        PaginatorInterface $paginator, 
        Request $request
    ): Response {

        $ingredients = $paginator->paginate(
            
            $repository->findBy(['user' => $this->getUser()]
            // ,['createdAt'=>'DESC']
            ),
            $request->query->getInt('page', 1),
            10
        );

        
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods:['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request, 
        EntityManagerInterface $manager
    ):Response {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();
            //affiche les ingrédients lié à l'utilisateur
            $ingredient->setUser($this->getUser());
            
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été ajouté avec succès!'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * This controller displays a form for modifying an ingredient
     *
     * @param EntityManagerInterface $manager
     * @param Ingredient $ingredient
     * @param Request $request
     * @return Response
     */
    #[IsGranted(
        new Expression("is_granted('ROLE_USER') and user === subject.getUser()"),
        subject:'ingredient',
    )]
    #[Route('/ingredient/edition/{id}', 'ingredient.edit', methods: ['GET','POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $manager,
        Ingredient $ingredient
    ) : Response {

        $form = $this->createForm(IngredientType::class,$ingredient);

        $form->handleRequest($request);
        //Autre façon de #[IsGranted()] avec une condition de bloquer l'accès aux ingrédient qui n'appartienne pas à l'utilisateur
        // if ($ingredient->getUser() !== $this->getUser()) {
        //     throw $this->createAccessDeniedException();        
        // }else{
            if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();
            
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été modifié avec succès!'
            );

            return $this->redirectToRoute('ingredient.index');
        }
    // }
        

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller can be used to delete an ingredient.
     *
     * @param EntityManagerInterface $manager
     * @param Ingredient $ingredient
     * @return Response
     */
    #[Route('ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    #[IsGranted(
        new Expression("is_granted('ROLE_USER') and user === subject.getUser()"),
        subject:'ingredient',
    )]
    public function delete(
        EntityManagerInterface $manager,
        Ingredient $ingredient
    ):Response{
        if ($ingredient->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();        
        }else{
            $manager->remove($ingredient);
            $manager->flush();
        }
        $this->addFlash(
            'success',
            'Votre ingrédient a été supprimé avec succès !'
        );

        return $this->redirectToRoute('ingredient.index');
    }
}
