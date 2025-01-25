<?php require "view_begin.php"; ?>
<?php require "view_menu.php"; ?>

<link rel="stylesheet" href="Content/css/profiles.css">

<script>
    function annulerModification() {
        window.location.href = '?controller=profile';
    }
</script>

<form action="?controller=profile&action=modifier_info" method="POST">
    <div class="card2 shadow">
        <div class="texte">
            <div class="titre">
                <div class="text-container h1"><h1>Modifier votre profil</h1> </div>
            </div>
        </div>
        <div class="texte">
            <div class="cercle">
                <img class="cercle" src="Content/img/<?= $data['photo_de_profil'] ?>" alt="Photo de profil">
            </div>
            <div class="information">
                <div class="sous-titre">
                    <div class="text-container h2" style="margin-bottom: 20px;"><?= $nom ?> <?= $prenom ?></div>
                    <!-- Ajout du code php pour pouvoir afficher le nom et le prenom -->
                </div>
                <div class="sous-titre">Adresse e-mail</div><!-- Ajout de la propriété "disabled et readonly" pour éviter de modifier l'email + de la balise style pour "griser" le fond de l'encadré -->
                <input class="encadrer" style="user-select:none; background-color: transparent; border: none" type="email" readonly    name="nouvelle_email" value="<?= $mail ?>" disabled>
                <div class="sous-titre">Nouveau mot de passe</div>
                <input class="encadrer" type="password" name="nouveau_mot_de_passe">
                <div class="sous-titre">Société principale</div>
                <input class="encadrer" type="text" name="nouvelle_societe" value="<?= (isset($societe) && $societe !== false) ? $societe['societe'] : ""; ?>" required>
            </div>
            <br>
            <button type="button" id="btnannuler" onclick="annulerModification()"> Annuler</button>
            <button type="submit" id="btnenregistrer"> Enregistrer</button>
        </div>
    </div>
</form>

<?php require "view_end.php"; ?>
