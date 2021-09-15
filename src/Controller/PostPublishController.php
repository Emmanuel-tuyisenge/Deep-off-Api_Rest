<?php

namespace App\Controller;

use App\Entity\Post;
use PhpParser\Node\Expr\PostInc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostPublishController extends AbstractController
{
    #[Route('/post/publish', name: 'post_publish')]
    public function index(): Response
    {
        return $this->render('post_publish/index.html.twig', [
            'controller_name' => 'PostPublishController',
        ]);
    }

    public function __invoke(Post $data): Post
    {
        $data->setOnline(true);
        return $data;
    }
}
