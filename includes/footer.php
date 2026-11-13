</main>

<footer class="bg-bordeaux text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h2 class="h5">Nos horaires</h2>
                <ul class="list-unstyled">
                    <?php foreach (getHoraires() as $h) : ?>
                        <li>
                            <?= e($h['jour']) ?> :
                            <?php if ($h['ferme']) : ?>
                                fermé
                            <?php else : ?>
                                <?= e(substr($h['heure_ouverture'] ?? '', 0, 5)) ?> - <?= e(substr($h['heure_fermeture'] ?? '', 0, 5)) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-6 mb-3">
                <h2 class="h5">Vite &amp; Gourmand</h2>
                <p>12 rue Sainte-Catherine, 33000 Bordeaux</p>
                <ul class="list-unstyled">
                    <li><a class="text-white" href="/mentions_legales.php">Mentions légales</a></li>
                    <li><a class="text-white" href="/cgv.php">Conditions générales de vente</a></li>
                    <li><a class="text-white" href="/contact.php">Nous contacter</a></li>
                </ul>
            </div>
        </div>
        <p class="text-center small mb-0">&copy; <?= date('Y') ?> Vite &amp; Gourmand</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
