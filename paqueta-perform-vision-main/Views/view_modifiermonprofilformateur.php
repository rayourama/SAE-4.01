<?php require "view_begin.php"; ?>
<?php require "view_menu.php"; ?>

<link rel="stylesheet" href="Content/css/profiles.css">



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
                 
            </div>
            <div class="sous-titre">Adresse e-mail</div>
            <input class="encadrer" style="user-select:none; background-color: transparent; border: none" type="email" readonly name="nouvelle_email" value="<?= $mail ?>" disabled>
            <div class="sous-titre">Nouveau mot de passe</div>
            <input class="encadrer" type="password" name="nouveau_mot_de_passe">
            <div class="sous-titre">LinkedIn</div>
            <input class="encadrer" type="text" name="nouveau_linkedin" value="<?php echo $formateur['linkedin']; ?>">
            <div class="sous-titre">CV</div>
            <input class="encadrer" type="file" name="nouveau_cv" accept=".pdf">
<div class="sous-titre">
    <div class="text-container h2">Compétence(s)</div>
    <p>Ajoutez une n+1ème compétence à chaque ajout d'une ou plusieurs compétences</p>
</div>
<div class="competences-container">
    <form id="skillsForm" method="post" action="?controller=profile&action=modifier_info" style="display: inline;position: relative;right: 100px;background-color:blue;">
    <input type="hidden" id="competences" name="competences"> 
        <input type="text" id="skillName" name="skillName" class="encadrer" placeholder="Ajoutez une ou plusieurs compétences">
        
        <label for="skillSpecialty" class="text-container h2">Spécialité:</label>
        <select id="skillSpecialty" name="skillSpecialty">
            <?php foreach ($themes as $theme) : ?>
                <option value="<?= htmlspecialchars($theme['id_theme']) ?>"><?= htmlspecialchars($theme['nom_theme']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="skillLevel">Niveau:</label>
        <select id="skillLevel" name="skillLevel">
            <?php foreach ($niveaux as $niveau) : ?>
                <option value="<?= htmlspecialchars($niveau['id_niveau']) ?>"><?= htmlspecialchars($niveau['libelle_niveau']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="button" onclick="ajouterCompetence()">Ajouter</button>
        <br>
        <ul id="competencesList">
        </ul>
    </form>

    <br>
    
</div>
</form>

<br>
            <button type="button" id="btnannuler" style="position:relative; bottom: 100px;" onclick="annulerModification()"> Annuler</button>
            <button type="submit" id="btnenregistrer" style="position:relative; bottom: 100px;"> Enregistrer</button>
        </div>
        </div>
    </div>
</form>

<script src="Content/script/modifiermonprofilformateur.js"></script>

<?php require "view_end.php"; ?>
