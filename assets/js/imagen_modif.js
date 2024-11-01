const defaultfile = 'assets/img/user.png';

const file = document.getElementById('imagen');
const img = document.getElementById('img');
img.addEventListener('load', e => {
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    } else {
        img.src = defaultfile;
    }
});