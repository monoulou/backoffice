<?php

namespace M2I\BlogBundle\Controller;

use M2I\BlogBundle\Entity\Article;
use M2I\BlogBundle\Entity\Comment;
use M2I\BlogBundle\Entity\Image;
use M2I\BlogBundle\Form\ArticleType;
use M2I\BlogBundle\Form\CommentType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{
    public function indexAction() {
        //On utilise toujours le container pour faire appel à un service.
        //doctrine est un service existant dans symfony.
        $doctrine = $this->container->get('doctrine');
        //EntityManager gere la relation entre bdd et entité/On fait donc appel à la methode qui permet de recuperer le Repository
        //Toujours recuperer l'EntityManager.
        $em = $doctrine->getManager();

        //On recupere le Repository qui permet d'accder aux methodes de requetage.
        $articleRepository = $em->getRepository('M2IBlogBundle:Article');
        $commentRepository = $em->getRepository('M2IBlogBundle:Comment');

        //Utilisation d'une methode qui fait une requete vers bdd et stock le resultat sous forme de tableau.
        //Utilisation du repository pour faire appel à la methode qui execute la requete souhaitée.
        $articleList = $articleRepository->findAll();
        //dump($articleList);
        //die();
        //$article1 = $articleRepository->findOneById(2);

        //Methode render qui renvoi vers une vue.La méthode peut prendre un second parametre qui ne peut etre qu'un tableau.
        return $this->render('M2IBlogBundle:Index:index.html.twig',
                              array('articleList'=>$articleList));
    }

    public function contactAction() {
       return $this->render('M2IBlogBundle:Index:contact.html.twig');
    }

    public function aboutAction() {
       return $this->render('M2IBlogBundle:Index:about.html.twig');
    }

    public function detailAction(Request $request, $idArticle) {

      $doctrine = $this->container->get('doctrine');
      $em = $doctrine->getManager();
      $articleRepository = $em->getRepository('M2IBlogBundle:Article');


      $detailArticle = $articleRepository->findOneById($idArticle);

      $comment = new Comment();
      //Pour faire une requete personnaliser.
      //Recuperer le Repository pour Comment(qui permet les requete SQL).
      $commentRepository = $em->getRepository('M2IBlogBundle:Comment');
      //Crée un nouvel objet qui contient le resultat de la requete contenue dans commentRepository créee au préalable.
      $lastCommentList = $commentRepository->myLastCommentList($detailArticle);


      $form = $this->container->get('form.factory')->create(CommentType::class,$comment);

      if($request->isMethod('POST')) {

          $form->handleRequest($request);//Hydratation de l'objet.

            if($form->isValid()) {

                  $doctrine = $this->container->get('doctrine');
                  $em = $doctrine->getManager();

                  $comment->setArticle($detailArticle);

                  $em->persist($comment);
                  $em->flush();

                  return $this->redirectToRoute('m2_i_blog_homepage');

            }
      }

      return $this->render('M2IBlogBundle:Index:detail.html.twig',
                            array ( 'detailArticle' => $detailArticle,
                                    'form'=>$form->createView(),
                                    'lastCommentList'=>$lastCommentList,
                                    )
                                  );
    }
    //fonction de test pour inserer en base de données.
    public function testAction() {
  /*
      //Création d'une nouvelle instance de la classe Article.
      $newArticle = new Article();
      //Modification du titre et de la description de ce nouvel objet à l'aide du setter.
      $newArticle->setTitle('Nouveau titre');
      $newArticle->setDescription('Nouvelle description');
      //Pour la date de création de ce nouvelle article, se referer au constructeur dans la classe.

      //Récupération de l'entity manager en passant par le service doctrine.
      $doctrine = $this->container->get('doctrine');
      //l'EntityManager sert a faire des requetes du type insert into
      //l'EntityManager sert à inserer en BDD
      $em = $doctrine->getManager();


      //Récupération de Repository qui permet les requetes du type SELECT...
      //Le Repository sert à aller chercher en BDD
      $articleRepository = $em->getRepository('M2IBlogBundle:Article');
      //Récupération d'un article par son id pour executer la modification souhaité.
      $toUpdateArticle = $articleRepository->findOneById(1);

      $toUpdateArticle->setTitle('Mon article');

      //On persist les actions souhaités à l'aide de l'entityManager.
      //Dans ce cas, j'ai persisté la mise à jour du titre.
      $em->persist($toUpdateArticle);
      //Dans ce cas je persist la création d'un nouvel article.
      //$em->persist($newArticle);

      //On flush.
      $em->flush();

*/


      $image = new Image();
      $image->setUrl('images/post2');
      $image->setAlt('description image');

      $article = new Article();
      $article->setTitle('Nouveau titre de l\'article');
      $article->setDescription('Nouveau contenu de l\'article');

      $doctrine = $this->container->get('doctrine');
      $em = $doctrine->getManager();

      //Faire la liaison entre l'article et l'image.
      //Accéder à l'attribut image de la classe article en passant par la methode set et lui passer le nouvel objet image
      $article->setImage($image);

      $em->persist($image);
      $em->persist($article);

      $em->flush();
      //J'utilise new response juste pour faire des tests.
      return new Response('<html><body></body></html>');

    }

    public function addAction(Request $request) {

      $article = new Article();

      //On récupère le container qui permet l'acces au service de creation de formulaire.
      $form = $this
      ->container
      ->get('form.factory')
      ->create(ArticleType::class, $article);//Fait le lien avec le formulaire déja crée en ligne de commande


      //si la requête est en post?
      if($request->isMethod('POST')) {
        //On créer le lien entre Requete <--> Formulaire.
       // A partir de maintenant, la variable $article est hydratée des valeurs saisie dans le formulaire
       $form->handleRequest($request);
       //dump($request);die();

       //Vérification que les valeurs saisie sont correctes.
       if($form->isValid()) {
            //Récupération du service doctrine.
            $doctrine = $this->container->get('doctrine');
            //Accès à l'entityManager qui gere les requete du type perist(INSERT INTO...)
            $em = $doctrine->getManager();


            $em->persist($article);

            $em->flush();

            //A la fin de l'envoi, la page est redirigée vers le formulaire vierge
            return $this->redirectToRoute('m2_i_blog_add_article');

          }
      }

      return $this->render('M2IBlogBundle:Index:add_article.html.twig',
                            array ('form' => $form->createView(),
                          ));

    }

    public function editArticleAction(Request $request, $idArticle) {


      $doctrine = $this->container->get('doctrine');

      $em = $doctrine->getManager();

      $articleRepository = $em->getRepository('M2IBlogBundle:Article');
      //Création d'un objet ($editArticle) qui correspond au contenu de l'entité récupérée en bdd par son id.
      $editArticle = $articleRepository->findOneById($idArticle);
      //dump($editArticle);die();


      //On récupère le container qui permet l'acces au service de creation de formulaire.
      $form = $this
        ->container
        ->get('form.factory')
        ->create(ArticleType::class, $editArticle);//Cette methode crée le lien entre l'objet instancié et...
                                                      // ...le formulaire crée.


      //si la requête est en post?
      if($request->isMethod('POST')) {
        //On créer le lien entre Requete <--> Formulaire.
       // A partir de maintenant, la variable $article est hydratée des valeurs saisie dans le formulaire
       $form->handleRequest($request);
       //dump($request);die();

       //Vérification que les valeurs saisies sont correctes.
       if($form->isValid()) {


            $em->persist($editArticle);

            $em->flush();

            //A la fin de l'envoi, la page est redirigée vers le formulaire vierge
            return $this->redirectToRoute('m2_i_blog_homepage');

        }

      }
      return $this->render('M2IBlogBundle:Index:edit_article.html.twig',
                          array ('myForm' => $form->createView(),
                          ));
        }

    public function removeAction($idArticle) {
      $doctrine = $this->container->get('doctrine');
      $em = $doctrine->getManager();
      $articleRepository = $em->getRepository('M2IBlogBundle:Article');

      $removeArticle = $articleRepository->findOneById($idArticle);

      $em->remove($removeArticle);
      $em->flush();

      return $this->redirectToRoute('m2_i_blog_homepage');
    }
}


//Lorsque qu'un methode a besoin de communiquer avec la base de données, toujours faire appel au service 'doctrine'
// ====> $doctrine = $this->container->get('doctrine').

//Puis toujours faire appel à l'EntityManager grace au service 'doctrine' pour atteindre le Repository
// =====> $em = $doctrine->getManager();
// =====> $articleRepository = $em->getRepository('M2IBlogBundle:Article');

//Le repository sert a faire des requetes.
// =====> $requete = $articleRepository->findAll()
//                                     ->findOneById


// Puis faire un persist de l'action(si action du type insert into)
// ====> $em->persist($toUpdateArticle)
// Puis faire un flush(dans tous les cas).
// ====> $em->flush()

//Lorsque la modification vient d'un formulaire, passer en parametre de la methode(Request $request).
// cf - construction d'un formulaire...
//...Puis effectuer la vérification des données saisies.
// ====> $request->isMethod('POST')) {
// ====>    $form->handleRequest($request);Hydratation d'un formulaire avec le contenu de la requete.
// ====>        if($form->isValid()) {...code...faire un persist, un flush, une redirection de route ...}
// ====> }
