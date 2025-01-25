<?php require "view_begin.php"; ?>
<?php require "view_menu.php"; ?>

<link rel="stylesheet" href="Content/css/competence.css">
<div class="container">

</br> </br>

  
         <h1>Mes Compétences</h1> 
       
    </div>

    </br>
</div>

<?php foreach ($competences as $categorie => $themes) : ?>
                <div class="encadrermonprofil">
                    <div class="txt-encadrer">
                        <?= htmlspecialchars($categorie) ?>
                        <?php foreach ($themes as $theme) : ?>
                            (<?= htmlspecialchars($theme['theme']) ?>:<?= htmlspecialchars($theme['niveau']) ?>)
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
   


<?php require "view_end.php"; ?>