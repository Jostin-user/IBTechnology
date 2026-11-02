<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noter la commande - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/commande.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <style>
        .review-item {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: var(--card-bg);
        }

        .product-review {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .product-info h4 {
            margin: 0 0 5px 0;
            color: var(--text);
        }

        .product-brand {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.9rem;
        }

        .rating-section {
            margin-bottom: 15px;
        }

        .rating-section label {
            display: block;
            color: var(--text);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .rating-stars {
            display: flex;
            gap: 5px;
        }

        .rating-stars input[type="radio"] {
            display: none;
        }

        .star {
            font-size: 25px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }

        .rating-stars input[type="radio"]:checked~.star,
        .star:hover,
        .star:hover~.star {
            color: #ffc107;
        }

        .comment-section label {
            display: block;
            color: var(--text);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .comment-section textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--background);
            color: var(--text);
            resize: vertical;
        }

        .comment-section textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
        }
    </style>
</head>
<?php require_once __DIR__ . "/../../layouts/header.php"; ?>

<body>

    <div class="account-container">
        <main class="orders-content">
            <h1>Noter les produits - Commande #<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?></h1>

            <div class="order-card">
                <form action="<?=BASE_URL?>commandes/soumettre-note/<?= $commande['id'] ?>" method="POST">
                    <h3>Donnez votre avis sur les produits</h3>

                    <?php foreach ($commande['produits'] as $index => $produit): ?>
                        <div class="review-item">
                            <div class="product-review">
                                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($produit['image'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($produit['nom']) ?>" class="product-image">
                                <div class="product-info">
                                    <h4><?= htmlspecialchars($produit['nom']) ?></h4>
                                    <p class="product-brand">Marque: <?= htmlspecialchars($produit['marque'] ?? 'N/A') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="rating-section">
                                <label>Note:</label>
                                <div class="rating-stars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?= $index ?>-<?= $i ?>"
                                            name="rating[<?= $produit['produit_id'] ?>]" value="<?= $i ?>" required>
                                        <label for="star<?= $index ?>-<?= $i ?>" class="star">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="comment-section">
                                <label for="comment<?= $index ?>">Commentaire (optionnel):</label>
                                <textarea id="comment<?= $index ?>" name="comment[<?= $produit['produit_id'] ?>]" rows="3"
                                    placeholder="Partagez votre expérience..."></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-actions">
                        <a href="../details/<?= $commande['id'] ?>" class="btn-detail">Annuler</a>
                        <button type="submit" class="btn-review">Soumettre les notes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    

    <?php require_once __DIR__ . "/../../layouts/footer.php"; ?>
</body>

</html>