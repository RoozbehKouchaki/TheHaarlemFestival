<?php

require_once 'BaseController.php';
require __DIR__ . '/../services/artistservice.php';
require __DIR__ . '/../services/albumservice.php';
require __DIR__ . '/../services/eventservice.php';
require __DIR__ . '/../services/shoppingcartservice.php';
include_once __DIR__ . '/../views/getURL.php';

class ArtistController extends BaseController
{
    private $artistService;
    private $albumService;
    private $eventService;
    private $cartService;

    function __construct()
    {
        parent::__construct();
        $this->artistService = new ArtistService();
        $this->albumService = new AlbumService();
        $this->eventService = new EventService();
        $this->cartService = new ShoppingCartService();
    }

    public function danceartists()
    {
        $model = $this->artistService->getAllDanceArtists();
        require __DIR__ . '/../views/dance/artistsoverview.php';
    }

    public function jazzartists()
    {
        $model = $this->artistService->getAllJazzArtists();
        require __DIR__ . '/../views/jazz/artistsoverview.php';
    }

    public function danceartistdetails()
    {
        $this->handleAddToCart($this->cartService);
        $params = $this->getUrlParams();
        $this->loadArtistDetails($params['id'], 'dance');
    }

    public function jazzartistdetails()
    {
        $this->handleAddToCart($this->cartService);
        $params = $this->getUrlParams();
        $this->loadArtistDetails($params['id'], 'jazz');
    }

    private function loadArtistDetails($id, $genre)
    {
        $model = $this->artistService->getOne($id);
        $albums = $this->albumService->getAllAlbumsByArtist($id);
        $events = $this->eventService->getEventsByArtistName('%' . $model->getName() . '%');
        require __DIR__ . "/../views/{$genre}/artistdetails.php";
    }

    public function artistcms()
    {
        if (isset($_POST["delete"])) {
            $this->deleteArtist();
        }

        if (isset($_POST["add"])) {
            $this->addArtist();
        }

        if (isset($_POST["update"])) {
            $this->updateArtist();
        }

        $updateArtist = null;
        if (isset($_POST["edit"])) {
            $updateArtist = $this->artistService->getAnArtist($_POST["edit"]);
        }

        $model = $this->getFilteredArtists();
        require __DIR__ . '/../views/cms/music/artist-cms.php';
    }

    private function getFilteredArtists()
    {
        if (isset($_POST["dance"])) {
            return $this->artistService->getAllDanceArtists();
        } elseif (isset($_POST["jazz"])) {
            return $this->artistService->getAllJazzArtists();
        }
        return $this->artistService->getAll();
    }

    public function addArtist()
    {
        $artist = $this->buildArtistFromPost($_POST);
        $this->handleArtistImages($artist);
        $success = $this->artistService->addArtist($artist);
        $this->showAlert($success, 'Artist added successfully.', 'Failed to add artist.');
    }

    public function updateArtist()
    {
        $id = $this->sanitize($_GET["updateID"]);
        $artist = $this->buildArtistFromPost($_POST);
        $existing = $this->artistService->getAnArtist($id);
        $this->handleArtistImages($artist, $existing);
        $success = $this->artistService->updateArtist($artist, $id) ? true : false;
        $this->showAlert($success, 'Artist updated successfully.', 'Failed to update artist.');
    }

    public function deleteArtist()
    {
        $id = $this->sanitize($_GET["deleteID"]);
        $success = $this->artistService->deleteArtist($id);
        $this->showAlert($success, 'Artist deleted successfully.', 'Failed to delete artist.');
    }

    private function buildArtistFromPost($post)
    {
        $artist = new Artist();
        $artist->setName($this->getPost("name", "changedName"));
        $artist->setDescription($this->getPost("description", "changedDescription"));
        $artist->setType($this->getPost("type", "changedType"));
        $artist->setSpotify($this->getPost("spotify", "changedSpotify"));
        return $artist;
    }

    private function handleArtistImages($artist, $existing = null)
    {
        $fields = [
            'headerImg' => 'setHeaderImg',
            'thumbnailImg' => 'setThumbnailImg',
            'logo' => 'setLogo',
            'image' => 'setImage',
        ];

        foreach ($fields as $field => $setter) {
            // Determine the field name for file upload (e.g., changedHeaderImg)
            $changedField = isset($_FILES["changed{$field}"]) ? "changed{$field}" : $field;
            // Attempt to upload a new image
            $imagePath = $this->handleImageUpload($changedField, $this->artistService, $existing ? $existing->{"get" . ucfirst($field)}() : null);
            if ($imagePath) {
                // If a new image was uploaded, use it.
                $artist->$setter($imagePath);
            } elseif ($existing) {
                // No new image; retain the existing image.
                $artist->$setter($existing->{"get" . ucfirst($field)}());
            }
        }
    }
    
    // New method to handle TinyMCE image uploads
    public function uploadImage()
    {
        // Check if the file is present and valid
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'No file uploaded or upload error.']);
            return;
        }
        
        $tmpName = $_FILES['file']['tmp_name'];
        $fileName = basename($_FILES['file']['name']);
        // Adjust the upload directory as needed; here we assume a public/uploads/ folder exists
        $uploadDir = __DIR__ . '/../../public/uploads/';
        $targetPath = $uploadDir . $fileName;
        
        // Move the uploaded file to the target directory
        if (!move_uploaded_file($tmpName, $targetPath)) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to move uploaded file.']);
            return;
        }
        
        // Create a URL accessible from the browser
        $url = '/uploads/' . $fileName;
        
        header('Content-Type: application/json');
        echo json_encode(['location' => $url]);
    }
}