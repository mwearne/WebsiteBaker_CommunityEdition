<?php
/**
 * WebsiteBaker Community Edition (WBCE)
 * Way Better Content Editing.
 * Visit http://wbce.org to learn more and to join the community.
 *
 * @copyright Ryan Djurovich (2004-2009)
 * @copyright WebsiteBaker Org. e.V. (2009-2015)
 * @copyright       WBCE Project (2015-2017)
 * @category        backend,hidden
 * @package         OpF Simple Backend
 * @version         1.0.0
 * @authors         Martin Hecht (mrbaseman)
 * @link            https://forum.wbce.org/viewtopic.php?id=176
 * @license         GNU GPL2 (or any later version)
 * @platform        WBCE 1.2.x 
 * @requirements    OutputFilter Dashboard 1.5.x and PHP 5.4 or higher
 *
 **/


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(!defined('WB_PATH')) {
        // Stop this file being access directly
        if(!headers_sent()) header("Location: ../index.php",TRUE,301);
        die('<head><title>Access denied</title></head><body><h2 style="color:red;margin:3em auto;text-align:center;">Cannot access this file directly</h2></body></html>');
}
/* -------------------------------------------------------- */


// Module description 
$module_description = 'A tool to configure the basic output filter of WB(CE)';
 
// Headings and text outputs
$OPF['HEADING']        = 'Valg: Filtrering av ut data';
$OPF['HOWTO']        = 'Du kan gj&oslash;re innstillinger for utdatafitreringen i valgene nedenfor.<p style="line-height:1.5em;"><strong>Tips: </strong>Mailto linker kan krypteres av en Javascript funksjon. For &aring; f&aring; benyttet denne funksjonen, m&aring; det legges til f&oslash;lgende PHP kode <code style="background:#FFA;color:#900;">&lt;?php register_frontend_modfiles(\'js\');?&gt;</code> inn i &lt;head&gt; seksjonen i index.php p&aring; design malen din. Uten denne modifikasjonen, vil kun @ karakterer i mailto linker bli erstattet.</p>';
$OPF['WARNING']        = '';

// Text and captions of form elements
$OPF['BASIC_CONF']        = 'Enkel Epost konfigurasjon';
$OPF['SYS_REL'] = 'Frontendoutput with relative Urls';
$OPF['EMAIL_FILTER']        = 'Filtrer Epost adresser i tekst';
$OPF['MAILTO_FILTER']        = 'Filtrer Epost adresser i mailto linker';
$OPF['ENABLED']        = 'P&aring;sl&aring;tt';
$OPF['DISABLED']        = 'Avsl&aring;tt';

$OPF['REPLACEMENT_CONF']= 'Endringe i Epost adresser';
$OPF['AT_REPLACEMENT']        = 'Bytt "@" med';
$OPF['DOT_REPLACEMENT']        = 'Bytt "." med';


$OPF['ALL_ON_OFF'] = 'Enable/Disable all old Outputfilter';
$OPF['DROPLETS'] = 'Droplets filter';
$OPF['WBLINK'] = 'WB-Link Filter';
$OPF['AUTO_PLACEHOLDER'] = 'Try to add placeholder for insert filter if they do not exist';
$OPF['INSERT'] = 'CSS, JS, Meta Insert Filter';
$OPF['JS_MAILTO'] = 'Use Javascript on Mailtofilter';
$OPF['SHORT_URL'] = 'Use short url filter';
$OPF['CSS_TO_HEAD'] = 'Use CSS to head';
