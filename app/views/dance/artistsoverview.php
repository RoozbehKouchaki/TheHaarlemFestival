<?php
include __DIR__ . '/../header.php';
?>

<div class="album py-5">
    <div class="container mb-5">
        <h1 class="text-dark text-center mb-5 display-3">Haarlem Festival Dance Artists</h1>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php
            foreach ($model as $artist) {
            ?>
                <div class="row col mb-3">
                    <div class="card shadow-sm">
                        <a href="/artist/danceartistdetails?id=<?= $artist->getId() ?>">
                            <img src="data:image/jpg;charset=utf8;base64,<?php echo base64_encode($artist->getThumbnailImg()); ?>" height="300px" width="348px">
                            <div class="card-body text-light bg-dark">
                                <p class="card-text fw-bold text-center"><?= $artist->getName() ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../footer.php';
?>