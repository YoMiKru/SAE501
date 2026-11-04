document.getElementById("captchaImage").onclick = function() {
    this.src = '/pages/captcha_generator.php?' + Math.random();
};
