<?php require "view_begin.php"; ?>

<link rel="stylesheet" href="Content/css/auth.css"/>

<nav>
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action = "?controller=auth&action=register" method="post">
                <nobr class="sign-up-title"><h1>Créer un compte</h1></nobr><br>
                <div class="container2">
                    <div class="tabs">
                        <input type="radio" id="radio-1" name="tabs" value="formateur" checked="">
                        <label class="tab" for="radio-1">Formateur</label>
                        <input type="radio" id="radio-2" name="tabs" value="client">
                        <label class="tab" for="radio-2">Client</label>
                        <span class="glider"></span>
                    </div>
                    <br>
                </div>
                <span></span>
                <input type="text" name="nom" placeholder="Nom" required/>
                <input type="text" name="prenom" placeholder="Prénom" required/>
                <input type="email" name="email" placeholder="Email" required/>
                <input type="password" name="password" placeholder="Mot de passe" required/>
                <ul class="conditions-mdp">
                    <li>8 caractères minimum</li>
                    <li>1 majuscule, 1 minuscule et 1 chiffre</li>
                    <li>1 caractère spécial</li>
                </ul><br>

                <input type="submit" value="S'enregistrer" class="blanc button" />
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="?controller=auth&action=login" method="post">
                <h1>Se connecter</h1>
                <input type="email" name="email" placeholder="Email" required/>
                <input type="password" name="password" placeholder="Mot de passe" required/>
                <a href="#">Mot de passe oublié ?</a>
                <input type="submit" value="Se connecter" class="blanc button" />
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Vous avez déjà un compte ?</h1>
                    <p>Pour rester connecter mettez vos informations personnelles</p>
                    <button class="ghost button" id="signIn">Se connecter</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Vous êtes nouveaux ?</h1>
                    <p>Inscrivez-vous pour publier ou consulter des formations</p>
                    <button class="ghost button" id="signUp">S'enregistrer</button>
                </div>
            </div>
        </div>
    </div>
</nav>

<script src="Content/script/auth.js"></script>

<?php require "view_end.php"; ?>
