<!DOCTYPE html>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>

<?php include __DIR__ . '/../../header.php'; ?>

<head>
    <link rel="stylesheet" href="/css/music_cms_style.css">
    <!-- Make sure to load Bootstrap CSS if needed -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
</head>

<h1 class="text-center mb-3">Manage Artists</h1>

<div class="center my-3">
    <form method="POST">
        <input type="submit" name="dance" value="Dance Artists" class="btn btn-primary mx-3 filterbtn">
    </form>
    <form method="POST">
        <input type="submit" name="jazz" value="Jazz Artists" class="btn btn-primary mx-3 filterbtn">
    </form>
</div>

<div class="album px-5">
    <div>
        <button class="btn btn-success mb-2" id="show-add-form">Add artist</button>
    </div>

    <!-- Hidden form to add a new artist -->
    <div id="form-add-container" style="display: none;">
        <form action="/artist/artistcms" method="POST" enctype="multipart/form-data">
            <!-- Add Artist Form Fields -->
            <div class="form-group row mb-1">
                <label for="name" class="col-sm-2 col-form-label">Name:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Insert Artist Name" required>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="description" class="col-sm-2 col-form-label">Description:</label>
                <div class="col-sm-10">
                    <!-- WYSIWYG field -->
                    <textarea class="form-control wysiwyg" id="description" name="description" placeholder="Insert Artist Details" required></textarea>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="type" class="col-sm-2 col-form-label">Type (dance/jazz):</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="type" name="type" placeholder="Insert Artist Type" required>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="headerImg" class="col-sm-2 col-form-label">HeaderImg:</label>
                <div class="col-sm-10">
                    <input type="file" class="form-control" id="headerImg" name="headerImg" required>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="thumbnailImg" class="col-sm-2 col-form-label">ThumbnailImg:</label>
                <div class="col-sm-10">
                    <input type="file" class="form-control" id="thumbnailImg" name="thumbnailImg" required>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="logo" class="col-sm-2 col-form-label">Logo:</label>
                <div class="col-sm-10">
                    <input type="file" class="form-control" id="logo" name="logo" required>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="spotify" class="col-sm-2 col-form-label">Spotify (link):</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="spotify" name="spotify" placeholder="Insert Spotify embedded song link" required>
                </div>
            </div>
            <div class="form-group row mb-1">
                <label for="image" class="col-sm-2 col-form-label">Image:</label>
                <div class="col-sm-10">
                    <input type="file" class="form-control" id="image" name="image" required>
                </div>
            </div>
            <input type="submit" name="add" value="Insert Artist" class="form-control btn btn-success mb-1">
        </form>
    </div>

    <!-- Display data -->
    <table class="table table-striped table-responsive">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">HeaderIMG</th>
                <th scope="col">ThumbnailIMG</th>
                <th scope="col">Logo</th>
                <th scope="col">Image</th>
                <th scope="col">Spotify</th>
                <th scope="col" colspan="2" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model as $artist): ?>
                <tr>
                    <td><?= $artist->getId() ?></td>
                    <td style="width:2%;"><?= $artist->getName() ?></td>
                    <td style="width:50%;"><?= $artist->getDescription() ?></td>
                    <td><?= '<img src="data:image/jpeg;base64,' . base64_encode($artist->getHeaderImg()) . '" width="150px"/>'; ?></td>
                    <td><?= '<img src="data:image/jpeg;base64,' . base64_encode($artist->getThumbnailImg()) . '" height="100px"/>'; ?></td>
                    <td><?= '<img src="data:image/jpeg;base64,' . base64_encode($artist->getLogo()) . '" width="100px"/>'; ?></td>
                    <td><?= '<img src="data:image/jpeg;base64,' . base64_encode($artist->getImage()) . '" height="100px"/>'; ?></td>
                    <td style="width:25%;">
                        <iframe style="border-radius:12px" src="<?= $artist->getSpotify() ?>" width="100%" height="300px" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
                    </td>
                    <td style="width:2%;">
                        <!-- Edit button toggles the inline edit form -->
                        <button class="btn btn-warning edit-toggle" data-id="<?= $artist->getId() ?>">Edit</button>
                    </td>
                    <td style="width:2%;">
                        <form action="/artist/artistcms?deleteID=<?= $artist->getId() ?>" method="POST">
                            <input type="hidden" name="delete" value="<?= $artist->getId() ?>">
                            <input type="submit" name="submit" value="Delete" class="btn btn-danger">
                        </form>
                    </td>
                </tr>
                <!-- Inline hidden edit form row -->
                <tr class="edit-row" id="edit-row-<?= $artist->getId() ?>" style="display: none;">
                    <td colspan="10">
                        <h3>Edit Artist #<?= $artist->getId() ?></h3>
                        <form action="/artist/artistcms?updateID=<?= $artist->getId() ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-group row mb-1">
                                <label for="changedName-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="changedName-<?= $artist->getId() ?>" name="changedName" value="<?= htmlspecialchars($artist->getName()) ?>" required>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="changedDescription-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">Description:</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control wysiwyg" name="changedDescription" id="changedDescription-<?= $artist->getId() ?>"><?= htmlspecialchars($artist->getDescription()) ?></textarea>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="changedType-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">Type (dance/jazz):</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="changedType-<?= $artist->getId() ?>" name="changedType" value="<?= htmlspecialchars($artist->getType()) ?>" required>
                                </div>
                            </div>
                            <!-- File inputs are not required to allow retaining current images -->
                            <div class="form-group row mb-1">
                                <label for="changedHeaderImg-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">HeaderImg:</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="changedHeaderImg-<?= $artist->getId() ?>" name="changedHeaderImg">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="changedThumbnailImg-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">ThumbnailImg:</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="changedThumbnailImg-<?= $artist->getId() ?>" name="changedThumbnailImg">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="changedLogo-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">Logo:</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="changedLogo-<?= $artist->getId() ?>" name="changedLogo">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="changedSpotify-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">Spotify:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="changedSpotify-<?= $artist->getId() ?>" name="changedSpotify" value="<?= htmlspecialchars($artist->getSpotify()) ?>" required>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="changedImage-<?= $artist->getId() ?>" class="col-sm-2 col-form-label">Image:</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="changedImage-<?= $artist->getId() ?>" name="changedImage">
                                </div>
                            </div>
                            <input type="submit" name="update" value="Update Artist" class="form-control btn btn-success mb-1">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Include TinyMCE with image upload configuration -->
<script src="https://cdn.tiny.cloud/1/9besn59rkonrm141er4q8our30fys6chuhr6mrohlaj4p2dw/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script><script>

document.addEventListener("DOMContentLoaded", function () {
    // Initialize TinyMCE for all visible textareas with class "wysiwyg" (e.g. in the add form)
    tinymce.init({
        selector: "textarea.wysiwyg",
        plugins: "lists link image code imagetools",
        toolbar: "undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code",
        menubar: false,
        images_upload_url: "/artist/uploadimage",   // Endpoint for image uploads
        automatic_uploads: true,
        images_upload_credentials: true
    });

    // Toggle Add Artist form
    $('#show-add-form').click(function() {
        $('#form-add-container').toggle();
    });

    // Toggle inline edit form for each artist row
    $('.edit-toggle').click(function() {
        var artistId = $(this).data('id');
        var editRow = $('#edit-row-' + artistId);
        editRow.toggle();

        // Initialize TinyMCE for the inline edit textarea if not already done
        var textareaSelector = '#changedDescription-' + artistId;
        if (editRow.is(':visible') && !tinymce.get(textareaSelector.substring(1))) {
            tinymce.init({
                selector: textareaSelector,
                plugins: "lists link image code imagetools",
                toolbar: "undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code",
                menubar: false,
                images_upload_url: "/artist/uploadimage",
                automatic_uploads: true,
                images_upload_credentials: true
            });
        }
    });
});
</script>

<?php include __DIR__ . '/../../footer.php'; ?>

<?php else: ?>
<div class="alert alert-danger mt-4 text-center">
    You do not have permission to access this CMS section.
</div>
<?php endif; ?>