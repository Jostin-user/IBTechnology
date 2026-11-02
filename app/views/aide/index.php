<?php require_once __DIR__ . '/../../includes/init.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Aide & Support - IbTechnologie</title>
    <meta name="description"
        content="Contactez notre support, consultez la FAQ et obtenez de l'aide pour vos commandes et produits.">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark-theme.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
</head>

<body class="theme-dark">
    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>

    <main class="container my-5">
        <h1>Aide & Support</h1>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert success"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_info'])): ?>
            <div class="alert" style="background:rgba(255,255,255,0.03);color:var(--muted);"><?php echo $_SESSION['flash_info']; unset($_SESSION['flash_info']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert error"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        <p>Bienvenue sur la page d'aide d'IbTechnologie. Vous trouverez ci-dessous les ressources et moyens de contact
            pour obtenir de l'aide.</p>

        <section id="faq" class="my-4">
            <h2>FAQ</h2>
            <ul>
                <li><strong>Comment passer une commande ?</strong>
                    <p>Ajoutez les produits au panier et depuis la page "Mon panier", cliquez sur "commander", entrez vos coordonnées d'adresse et validez.</p>
                </li>
                <li><strong>Quelle est la politique de retour ?</strong>
                    <p>Les retours sont acceptés sous 14 jours suivant la réception. Contactez le support pour lancer un
                        retour.</p>
                </li>
                <li><strong>Méthodes de paiement disponibles</strong>
                    <p>Nous acceptons pour l'nstant le payement à la livraison uniquement. Les paiements mobiles ( Orange, MTN) seront disponibles dans les prochains jours.</p>
                </li>
            </ul>
        </section>

        <section id="contact" class="my-4">
            <h2>Contactez-nous</h2>
            <p>Pour toute question, envoyez un message via le formulaire ci-dessous ou contactez-nous par téléphone.</p>

            <form method="POST" action="<?= BASE_URL ?>contact/send">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" rows="6" class="form-control" required></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Envoyer</button>
            </form>
        </section>

        <section id="support-telephonique" class="my-4">
            <h2>Support téléphonique</h2>
            <p>Appeler: <strong>+226 00 00 00 00</strong> (Lundi - Vendredi: 9h - 17h)</p>
            <p>Ou laissez nous un message sur whatsapp au <strong>+226 00 00 00 00</strong></p>
        </section>
    </main>

    <?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
</body>

</html>