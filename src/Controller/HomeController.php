<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostRepository $repository): Response
    {
        $posts = $repository->findAll();
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('article/{slug}-{id}', name: 'show', requirements: ['slug' => '[a-z0-9\-]+', 'id' => '\d+'])]
    public function show(string $slug, int $id, PostRepository $repository): Response
    {
        $article = $repository->find($id);
        // redirect root if slug is not correct
        if($article->getSlug() !== $slug) {
            return $this->redirectToRoute('show', [
                'slug' => $article->getSlug(),
                'id' => $article->getId(),
            ], 301);
        }
        return $this->render('show/index.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        // create form with post data
        $form = $this->createForm(PostType::class, $post);
        // post form data modify
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $post->setUpdatedAt(new \DateTimeImmutable());
            $this->addFlash('success', 'L\'article a bien été modifié');
            return $this->redirectToRoute('show', [
                'slug' => $post->getSlug(),
                'id' => $post->getId(),
            ]);
        }
        return $this->render('admin/edit.html.twig', [
            'id' => $post,
            'form' => $form,
        ]);
    }

    // function create
    #[Route('create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();
            $post->setUpdatedAt(new \DateTimeImmutable());
            $post->setCreatedAt(new \DateTimeImmutable());
            $this->addFlash('success', 'L\'article a bien été créé');
            return $this->redirectToRoute('home');
        }
        return $this->render('admin/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('edit/{id}', name: 'delete', methods: ["DELETE"])]
    public function delete(Post $post, EntityManagerInterface $em): Response
    {
        $em->remove($post);
        $em->flush();
        $this->addFlash('success', 'L\'article a bien été supprimé');
        return $this->redirectToRoute('home');
    }
}
