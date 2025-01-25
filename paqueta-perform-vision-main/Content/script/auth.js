$(document).ready(function () {

    const $signUpButton=$('#signUp');
    const $signInButton=$('#signIn');
    const $container=$('#container');

    if($signUpButton && $signInButton && $container) {
        $signUpButton.on('click',()=>{
            $container.addClass('right-panel-active');
        });

       $signInButton.on('click',()=>{
            $container.removeClass('right-panel-active');
        });
    }
});



