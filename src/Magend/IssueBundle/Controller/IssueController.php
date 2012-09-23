<?php

namespace Magend\IssueBundle\Controller;

use Exception;
use ZipArchive;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
use Magend\PageBundle\Entity\Page;
use Magend\IssueBundle\Form\IssueType;
use Magend\IssueBundle\Entity\Issue;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\IssueBundle\Util\SimpleImage;
use Doctrine\ORM\NoResultException;

/**
 * 
 * @Route("/issue")
 * @author kail
 */
class IssueController extends Controller
{
    private function _findIssue($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        
        return $issue;
    }
    
    /**
     * 
     * @Route("/{id}/insert-copyright", name="insert_copyright")
     */
    public function insertCopyrightAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            throw new Exception('Issue not found'); 
        }
        
        $cpr = $issue->getMagazine()->getCopyrightArticle();
        $articleIds = $issue->getArticleIds();
        if (in_array($cpr->getId(), $articleIds)) {
            throw new Exception('Already inserted'); 
        }
        $issue->addArticle($cpr);
        $articleIds[] = $cpr->getId(); 
        $issue->setArticleIds($articleIds);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $issue->getId())));
    }
    
    /**
     * Remove copyright article
     *
     * @Route("/{id}/remove-copyright", name="remove_copyright", requirements={"id"="\d+"})
     */
    public function removeCopyrightAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            throw new Exception('Issue not found'); 
        }
        
        $cpArticle = $issue->getMagazine()->getCopyrightArticle();
        $issue->removeArticle($cpArticle);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $id)));
    }
    
    private function copyResource($issueId, $resourceFile)
    {
        if ($resourceFile == null) return;
        
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $uploadDir = $rootDir . '/../web/uploads/';
        $filePath = $uploadDir . $resourceFile;
        if (!file_exists($filePath)) return;
        
        return copy($filePath, $uploadDir . $issueId . '/' . $resourceFile);
    }
    
    /**
     * Pre-publish the issue but not update issue.publish
     * 
     * @Route("/{id}/prepublish", name="issue_prepublish", defaults={"_format" = "json"})
     */
    public function prePublishAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            return new Response('{"msg":"期刊不存在"}'); 
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $issue->setPublishMode(Issue::PUBLISH_PREVIEW);
        $em->flush();
        
        // output magazine's xml
        $this->_outputGroupXml($issue);
        
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $publishDir = $rootDir . '/../web/Publish/';
        // update version file
        $vm = $this->get('magend.version_manager');
        $vm->incIssueVersion();
        file_put_contents($publishDir . 'version.xml', $vm->getVersionFileContents());
        
        $zipName = $this->compressIssueAssets($issue);
        $pubAt = $issue->getPublishedAt()->format('Y-m-d');
        return new Response(json_encode(array(
            'msg' => '发布成功',
            'publishedAt' => $pubAt,
            'zip' => $zipName
        )));
    }
    
    private function sureRemoveDir($dir, $DeleteMe = false)
    {
        if(!$dh = @opendir($dir)) return;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..') continue;
            if (!@unlink($dir.'/'.$obj)) $this->sureRemoveDir($dir.'/'.$obj, true);
        }
    
        closedir($dh);
        if ($DeleteMe){
            @rmdir($dir);
        }
    }
    
    /**
     * Compress all asset files of the issue
     * 
     * @param Issue $issue
     * @return string
     */
    private function compressIssueAssets($issue)
    {
        $id = $issue->getId();
        $em = $this->getDoctrine()->getEntityManager();
        
        // zip issue assets
        // 1. copy assets into same folder
        $query = $em->createQuery('SELECT s, a, p, h FROM MagendIssueBundle:Issue s LEFT JOIN s.articles a LEFT JOIN a.pages p LEFT JOIN p.hots h WHERE s = :issue')
                    ->setParameter('issue', $issue);
        $issue = $query->getSingleResult();
        
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $uploadDir = $rootDir . '/../web/uploads/';
        if (!file_exists($uploadDir . $id)) {
            mkdir($uploadDir . $id);
        } else {
            $this->sureRemoveDir($uploadDir . $id);
        }
        $publishDir = $rootDir . '/../web/Publish/';
        $zipName = $publishDir . "issue$id.zip";
        if (file_exists($zipName)) {
            @unlink($zipName);
        }
        
        // output issue's xml
        $om = $this->get('magend.output_manager');
        $response = $om->outputIssue($id);
        file_put_contents($publishDir . "issue$id.xml", $response->getContent());
        
        $this->copyResource($id, $issue->getAudio());
        $this->copyResource($id, $issue->getPortraitCover());
        $this->copyResource($id, $issue->getLandscapeCover());
        $this->copyResource($id, $issue->getPreview());
        $this->copyResource($id, $issue->getEnPreview());
        
        $articles = $issue->getArticles();
        foreach ($articles as $article) {
            // output article xml
            $response = $om->outputArticle($article->getId());
            $aid = $article->getId();
            file_put_contents($publishDir . "article$aid.xml", $response->getContent());
            
            $article->getAudio();
            $pages = array_merge($article->getPages(), $article->getInfoPages(), $article->getStructurePages());
            if (empty($pages)) continue;
            
            foreach ($pages as $page) {
                $this->copyResource($id, $page->getLandscapeImg());
                // @todo refactor, DRY
                if ($page->getCustomLandscapeImgThumbnail() == null && $page->getLandscapeImg() != null) {
                    $thumb = new SimpleImage();
                    $thumb->load($rootDir . '/../web/uploads/' . $page->getLandscapeImg());
                    $imagineFilters = $this->container->getParameter('imagine.filters');
                    list($width, $height) = $imagineFilters['landscapeThumb']['options']['size'];
                    $thumb->resize($width, $height);
                    list($uniqName, $ext) = explode('.', $page->getLandscapeImg());
                    $thumbName = $uniqName . "_thumb.$ext";
                    $thumb->save($rootDir . '/../web/uploads/' . $thumbName);
                    
                    $this->copyResource($id, $thumbName);
                } else {
                    $this->copyResource($id, $page->getLandscapeImgThumbnail());
                }
                
                $this->copyResource($id, $page->getPortraitImg());
                if ($page->getCustomPortraitImgThumbnail() == null && $page->getPortraitImg() != null) {
                    $thumb = new SimpleImage();
                    $thumb->load($rootDir . '/../web/uploads/' . $page->getPortraitImg());
                    $imagineFilters = $this->container->getParameter('imagine.filters');
                    list($width, $height) = $imagineFilters['landscapeThumb']['options']['size'];
                    $thumb->resize($width, $height);
                    list($uniqName, $ext) = explode('.', $page->getPortraitImg());
                    $thumbName = $uniqName . "_thumb.$ext";
                    $thumb->save($rootDir . '/../web/uploads/' . $thumbName);
                    
                    $this->copyResource($id, $thumbName);
                } else {
                    $this->copyResource($id, $page->getPortraitImgThumbnail());
                }
                
                
                $hots = $page->getHots();
                if (empty($hots)) continue;
                foreach ($hots as $hot) {
                    $assets = $hot->getAssets(false);
                    if (empty($assets)) continue;
                    foreach ($assets as $asset) {
                        $this->copyResource($id, $asset->getResource());
                    }
                }
            }
        }
        
        // 2. zip folder of assets
        $zip = new ZipArchive();
        if (!$zip->open($zipName, ZIPARCHIVE::CREATE)) {
            exit("cannot open <$zipName>\n");
        }
        foreach (glob("$uploadDir$id/*") as $file) {
            $zippedFile = substr($file, strrpos($file, '/') + 1);
            $zip->addFile($file, $zippedFile);
        }
        if (!$zip->status == ZIPARCHIVE::ER_OK) {
            exit("Failed to write files to zip\n");
        }
        $zip->close();
        
        return $zipName;
    } 
    
    /**
     * Output issue's magazine's xml
     * 
     * @param Issue $issue
     */
    private function _outputGroupXml($issue)
    {
        $om = $this->get('magend.output_manager');
        $om->outputMagazinesXML();
        $om->outputMagazineXML($issue->getMagazine()->getId());
    }
    
    /**
     * 
     * @Route("/{id}/publish", name="issue_publish", defaults={"_format" = "json"})
     */
    public function publishAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            return new Response('{"msg":"期刊不存在"}'); 
        }
        if ($issue->isPublishedOfficially()) {
            return new Response('{"msg":"期刊已正式发布"}'); 
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        if ($user->getCorp() && $user->getCorp()->isTrial()) {
            return new Response('{"msg":"对不起，试用用户不能发布期刊"}');
        }
        
        $issue->setPublishMode(Issue::PUBLISH_OFFICIAL);
        if ($issue->getPublishedAt() === null) {
            $issue->setPublishedAt(new \DateTime());
        }
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        // output magazine's xml
        $this->_outputGroupXml($issue);
        $zipName = $this->compressIssueAssets($issue);
        
        // update version file
        $vm = $this->get('magend.version_manager');
        $vm->incIssueVersion();
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $publishDir = $rootDir . '/../web/Publish/';
        file_put_contents($publishDir . 'version.xml', $vm->getVersionFileContents());
        
        $pubAt = $issue->getPublishedAt()->format('Y-m-d');
        return new Response(json_encode(array(
            'msg' => '发布成功',
            'publishedAt' => $pubAt,
            'zip' => $zipName
        )));
    }
    
    /**
     * Abstract new and edit actions
     * 
     * @param Issue $issue
     */
    private function _formRet($issue)
    {
        $form = $this->createForm(new IssueType(), $issue);
        if ($this->getRequest()->getMethod() == 'POST') {
            $ret = $this->_submit($form, $issue);
            if ($ret) {
                return $ret;
            }
        }
        /*
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');
        if ($isAdmin) {
            $magRepo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
            $mags = $magRepo->findAll();
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
            $dql = 'SELECT m FROM MagendMagazineBundle:Magazine m LEFT JOIN m.staffUsers u WHERE m.owner = :user OR u = :user';
            $em = $this->getDoctrine()->getEntityManager();
            $q = $em->createQuery($dql)->setParameter('user', $user);
            $mags = $q->getResult();
        }*/
        return array(
            'issue'    => $issue,
            'form'     => $form->createView()
        );        
    }
    
    /**
     * Delete cover or preview
     * @Route("/delImg", name="issue_imgDel", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function delImgAction()
    {
        $req = $this->getRequest();
        $issueId = $req->get('id');
        $issue = $this->_findIssue($issueId);
        if (empty($issue)) {
            return new Response('no issue');
        }
        
        $img = $req->get('img');
        $getter = "get$img";
        if (method_exists($issue, $getter)) {
            $imgName = $issue->$getter();
            $setter = "set$img";
            $issue->$setter(null);
            $rootDir = $this->container->getParameter('kernel.root_dir');
            @unlink($rootDir . '/../web/uploads/'. $imgName);
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();        
        }
        
        return new Response('done');
    }
    
    /**
     * Upload cover (landscape or portrait, not both by dnd), and preview images
     * 
     * @todo refactor all dnd uploads
     * @Route("/uploadImg", name="issue_imgUpload", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function uploadImgAction()
    {
        $req = $this->getRequest();
        $issueId = $req->get('id');
        $landscapeCover = $req->files->get('landscapeCover');
        $portraitCover = $req->files->get('portraitCover');
        $preview = $req->files->get('preview');
        $enPreview = $req->files->get('enPreview');
        if ($issueId === null || (empty($landscapeCover) && empty($portraitCover) && empty($preview) && empty($enPreview))) {
            return new Response('no file');
        }
        
        $issue = $this->_findIssue($issueId);
        if (empty($issue)) {
            return new Response('no issue');
        }
        
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $oldImg = null;
        $imgFile = null;
        $setterName = null;
        if ($landscapeCover) {
            $oldImg = $issue->getLandscapeCover();
            $imgFile = $landscapeCover;
            $setterName = 'setLandscapeCover';
        } else if ($portraitCover) {
            $oldImg = $issue->getPortraitCover();
            $imgFile = $portraitCover;
            $setterName = 'setPortraitCover';
        } else if ($preview) {
            $oldImg = $issue->getPreview();
            $imgFile = $preview;
            $setterName = 'setPreview';
        } else if ($enPreview) {
            $oldImg = $issue->getEnPreview();
            $imgFile = $enPreview;
            $setterName = 'setEnPreview';
        }
        if (!empty($oldImg)) {
            @unlink("$rootDir/../web/uploads/$oldImg");
        }
        
        $imgName = uniqid('issue_') . '.' . $imgFile->guessExtension();
        $imgFile->move($rootDir . '/../web/uploads/', $imgName);
        $issue->$setterName($imgName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        $tplVars = array(
            'img' => $req->getBasePath() . '/uploads/' . $imgName
        );
        return new Response(json_encode($tplVars));
    }
    
    /**
     * Upload audio
     * 
     * @todo refactor with article audio upload
     * @Route("/uploadAudio", name="issue_audioUpload", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function uploadAudioAction()
    {
        $req = $this->getRequest();
        $issueId = $req->get('id');
        $file = $req->files->get('file');
        if ($issueId === null || empty($file)) {
            return new Response('no file');
        }
        
        $issue = $this->_findIssue($issueId);
        if (empty($issue)) {
            return new Response('no issue');
        }
        
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $oldAudio = $issue->getAudio();
        if (!empty($oldAudio)) {
            @unlink("$rootDir/../web/uploads/$oldAudio");
        }
        
        $originalName = $file->getClientOriginalName();
        $audioName = uniqid('audio_') . '.' . $file->guessExtension();
        $file->move($rootDir . '/../web/uploads/', $audioName);
        $issue->setAudio($audioName);
        $issue->setAudioName($originalName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        $tplVars = array(
            'audio' => $req->getBasePath() . '/uploads/' . $audioName,
            'name' => $originalName
        );
        return new Response(json_encode($tplVars));
    }
    
    /**
     * Get default issue nos
     * 
     * @Route("/nos", name="issuenos", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function issueNoAction()
    {
        $req = $this->getRequest();
        $magId = $req->get('magazineId');
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT s FROM MagendIssueBundle:Issue s WHERE s.magazine = :magId ORDER BY s.totalIssueNo DESC')
                    ->setParameter('magId', $magId)
                    ->setMaxResults(1);
        try {
            $issue = $query->getSingleResult();
        } catch (\ Exception $e) {
            return new Response('');
        }
        
        $tplVars = array(
            'yearIssueNo' => $issue->getYearIssueNo(),
            'totalIssueNo' => $issue->getTotalIssueNo()
        );
        
        return new Response(json_encode($tplVars));
    }
    
    /**
     * $id is magazine id
     * 
     * @Route("/magazine/{id}/new", name="issue_new", requirements={"id"="\d+"})
     * @Template()
     */
    public function newAction($id)
    {
        $req = $this->getRequest();
        $issue = new Issue();
        
        $magRepo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
        $mag = $magRepo->find($id);
        $issue->setMagazine($mag);
        
        $em = $this->getDoctrine()->getEntityManager();
        if ($req->getMethod() == 'GET') {
            $query = $em->createQuery('SELECT s FROM MagendIssueBundle:Issue s WHERE s.magazine = :magId ORDER BY s.totalIssueNo DESC')
                        ->setParameter('magId', $id)
                        ->setMaxResults(1);
            try {
                $latestIssue = $query->getSingleResult();
                
                $totalIssueNo = $latestIssue->getTotalIssueNo();
                $yearIssueNo = $latestIssue->getYearIssueNo();
                //$yearIssueNo
                $issue->setYearIssueNo($yearIssueNo);
                $issue->setTotalIssueNo($totalIssueNo + 1);
            } catch (Exception $e) {
                // do nothing
            }
        }
        
        return $this->_formRet($issue);
    }
    
    /**
     * 
     * $id is issue id
     * 
     * @Route("/{id}/edit", name="issue_edit")
     * @Template("MagendIssueBundle:Issue:new.html.twig")
     */
    public function editAction($id)
    {
        $issue = $this->_findIssue($id);
        
        if ($issue->isPublishedOfficially()) {
            return $this->container->get('templating')->renderResponse(
                'MagendIssueBundle:Issue:noedit.html.twig'
            );
        }
        
        return $this->_formRet($issue);
    }
    
    /**
     * For new and edit
     * 
     */
    private function _submit($form, $issue)
    {
        $req = $this->getRequest();
        $form->bindRequest($req);
        if ($form->isValid()) {             
            $em = $this->getDoctrine()->getEntityManager();
            if (empty($issue) || $issue->getId() == null) {
                $magazineId = $req->get('id');
                $magRepo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
                $mag = $magRepo->find($magazineId);
                if (empty($mag)) {
                    throw new Exception('magazine ' . $magazineId . ' not found');
                }
                $issue->setMagazine($mag);
                $em->persist($issue);
            }
            $em->flush();
            
            if ($req->isXmlHTTPRequest()) {
                $id = $issue->getId();
                $response = new Response(json_encode(array(
                    'id'        => $id,
                    'editorUrl' => $this->generateUrl('issue_editor', array('id' => $id))
                )));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                return $this->redirect($this->generateUrl('issue_edit', array('id' => $issue->getId()))); //issue_article_list
            }
        }
        //var_dump( $form->getErrors() );exit;
        return null;
    }
    
    /**
     * Only return articleIds if by ajax
     * 
     * @Route("/{id}", name="issue_show", requirements={"id"="\d+"})
     * @Template()
     */
    public function showAction($id)
    {        
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new Exception('issue ' . $id . ' not found');
        }
        
        $req = $this->getRequest();
        if ($req->isXmlHTTPRequest()) {
            return new Response(json_encode(array(
                'articleIds' => $issue->getArticleIds()
            )));
        }
        
        return array(
            'issue' => $issue
        );
    }
    
    /**
     * Deprecated
     * 
     * @Route("/list", name="issue_list")
     * @Template()
     */
    public function listAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        $magId = $this->getRequest()->cookies->get('magazine_id');
        
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');
        if ($magId !== null) {
            if ($isAdmin) {
                $dql = 'SELECT m FROM MagendMagazineBundle:Magazine m  WHERE m.id = :mag';
                $q = $em->createQuery($dql)->setParameter('mag', $magId);
            } else {
                $dql = 'SELECT m FROM MagendMagazineBundle:Magazine m LEFT JOIN m.staffUsers u WHERE (m.owner = :user OR u = :user) AND m.id = :mag';
                $q = $em->createQuery($dql)->setParameter('user', $user->getId())->setParameter('mag', $magId);
            }
            try {
                $mag = $q->getSingleResult();
            } catch (Exception $e) {
                $mag = null;
            }
            if ($mag == null) {
                $magId = null;
            }
        }
        
        if ($magId === null) {
            $where = $isAdmin ? '' : 'LEFT JOIN m.staffUsers u WHERE m.owner = :user OR u = :user';
            $params = $isAdmin ? array() : array('user' => $user->getId());
            $dql = 'SELECT m.id FROM MagendMagazineBundle:Magazine m '. $where . ' ORDER BY m.createdAt DESC';
            $query = $em->createQuery($dql)->setParameters($params);
            $query->setMaxResults(1);
            try {
                $magId = $query->getSingleScalarResult();
            } catch (NoResultException $e) {
                return array();
            }
        }
        
        return new RedirectResponse($this->generateUrl('magazine_issues', array(
            'id' => $magId
        )));
    }
    
    /**
     * @Route("/update_articleIds", name="issue_update_articleIds", defaults={"_format"="json"})
     * @method("post")
     */
    public function updateArticleIdsAction()
    {
        $req = $this->getRequest();
        $id = $req->get('id');
        
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new Exception('issue ' . $id . ' not found');
        }
        
        $issue->setArticleIds($req->get('articleIds'));
        $em->flush();
        return new Response();
    }
    
    /**
     * Get issue's articles with article's pages, 
     * ordered according to ids text
     * 
     * @Route("/{id}/articles", name="issue_articles", defaults={"_format"="json"})
     */
    public function articlesAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'SELECT partial a.{id, title}, partial p.{id, landscapeImg, portraitImg, label} FROM MagendArticleBundle:Article a INDEX BY a.id LEFT JOIN a.pages p WHERE :issueId MEMBER OF a.issues';
        $query = $em->createQuery($dql)->setParameter('issueId', $id);
        
        $arr = $query->getArrayResult();
        $result = array();
        // order articles according to article_ids, order 
        foreach ($arr as $articleId=>$articleArr) {
            $pages = array_values($articleArr['pages']);
            $articleArr['pages'] = $pages;
            $result[] = $articleArr;
        }
        
        /*
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if ($issue == null) {
            return new Response('');
        }
        */
        return new Response(json_encode($result));
    }
    
    /**
     * 
     * @Route("/{id}/article/list", name="issue_article_list", requirements={"id"="\d+"})
     * @Template()
     */
    public function articleListAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new Exception('issue ' . $id . ' not found');
        }
        
        // select pages and associate with articles
        $articleIds = $issue->getArticleIds();
        if (!empty($articleIds)) {
            $em = $this->getDoctrine()->getEntityManager();
            $dql = 'SELECT a, p FROM MagendArticleBundle:Article a LEFT JOIN a.pages p WHERE a in (:articles)';
            $q = $em->createQuery($dql)->setParameter('articles', $articleIds);
            $q->getResult();
        }
        
        // select keywords and associate with articles
        // shit, I cannot get this done - if article has no keywords, then article->getKeywords() will
        // still query database
        /*
        $dql = 'SELECT a, k FROM MagendArticleBundle:Article a LEFT JOIN a.keywords k WHERE a in (:articles)';
        $q = $em->createQuery($dql)->setParameter('articles', $issue->getArticleIds());
        $articles = $q->getResult();*/
        
        return array('issue' => $issue);
    }
    
    /**
     * 
     * @Route("/{id}/layout", name="issue_layout", requirements={"id"="\d+"}, defaults={"_format"="json"})
     */
    public function layoutAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new Exception('issue ' . $id . ' not found');
        }

        $req = $this->getRequest();
        $articles = $req->get('articles');
        if (empty($articles)) {
            return new Response('{ "error":1 }');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT a FROM MagendArticleBundle:Article a INDEX BY a.id WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        $arts = $query->getResult();
        
        foreach ($articles as $articleId=>$pageIds) {
            if (isset($arts[$articleId])) {
                $pages = $arts[$articleId]->getPages();
                $seq = 1;
                foreach ($pageIds as $pageId) {
                    $pages[$pageId]->setSeq($seq);
                    ++$seq;
                }
            }
        }
        $articleIds = $req->get('articleIds');
        if ($issue->getArticleIds() != $articleIds) {
            $issue->setArticleIds($articleIds);
        }
        $em->flush();
        
        return new Response('{ "success":1 }');
    }
    
    /**
     * Delete all articles and pages belong to this issue
     * May need provide method to just delete issue and associations with articles,
     * not article themselves
     * // , defaults={"_format"="json"}
     * @Route("/{id}/flush", name="issue_flush")
     */
    public function flushAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new Exception('No such issue');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT partial a.{id} FROM MagendArticleBundle:Article a WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        $arts = $query->getResult();
        foreach ($arts as $art) {
            $em->remove($art);
        }
        
        $em->remove($issue);        
        $em->flush();
        
        $om = $this->get('magend.output_manager');
        $om->outputMagazineXML($issue->getMagazine()->getId());
        
        return $this->redirect($this->generateUrl('issue_list'));
    }
    
    /**
     * @Route("/{id}/editor", name="issue_editor")
     * @Template()
     */
    public function editorAction($id)
    {
        $issue = $this->_findIssue($id);
        if (!$issue) {
            throw new Exception('issue ' . $id . ' not found');
        }
        
        return array(
            'issue' => $issue
        );
    }
}
