/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
//import '../css/app.css';


// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
//import $ from 'jquery';

// 3 lignes de code ci-dessous ne fonctionne pas, import de jquery et jquery ui en cdn...
var $ = require('jquery');
global.$ = global.jQuery = $; // variable de webpack pour que jquery soit accessible en dehors du module webpack en accédant à l'objet global.
require('bootstrap');


// import './jquery.min.js';
// import './jquery-ui.min.js';
// import './bootstrap.min.js';
// import './bootstrap-datepicker.min.js';


// require('./jquery.min.js');
// require('popper.js');
//require('./bootstrap.min.js');

//require('./bootstrap-datepicker.min.js');
// require('./js/ad.js');



