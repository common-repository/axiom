<?php
/*
Plugin Name: Axiom
Description: Axiom is derived from a plugin which uses PHP Math Publisher.  It finds ASCII formulas in posts between [m][/m] tags and converts the ASCII to images using the <a href="http://www.xm1math.net/phpmathpublisher/">PHP Math Publisher</a>. Syntax help can be found in this plugin's director under /phpmathpublisher/doc/index.html. Axiom is based on the plugin written by <a href="http://www.slug.it/naufraghi/">Matteo Bertini</a>. For the full lineage of the code see the comments in the ReadMe file.
Plugin URI: http://www.slug.it/naufraghi/programmazione-web/wpmathpublisher
Version: 1.0
Author: Randy Morrow
Author URI: http://randy.rlmdev.com
*/

/*  Copyright 2008  Randy Morrow  (email : rjmatm@yahoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
  Notes:
  I have made some major additions and some small changes to the code.
  I would say that Bertini would recognize very little of this code as his own.
  I think it justifies changing the name but I still give credit to the original
  author as I could not get mine to work properly with PHPMathPublisher.
   -- Randy Morrow
*/

//######## CONFIG START ########

$basedir = dirname(__FILE__);

$webdir = str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'] . '/', $basedir);

//######## CONFIG  END  ########

// Include the PHP Math Publisher library
// See: http://www.xm1math.net/phpmathpublisher/
include_once("mathpublisher.php") ;

//Overwrite dir locations
$dirfonts=$basedir."/fonts";
$dirimg=$basedir."/img";

function wpmathfilter($ascii_math, $fsize=12)
{
    global $basedir;
	global $webdir;

    //mathfilter($text, $size, $pathtoimg)
	$phpmath = mathfilter("<m>".$ascii_math."</m>", $fsize, $webdir ."/img/");
	return $phpmath;
}

function to_phpmath($content)
{
	
	$attribList = array('size');
	
	$attribListCnt = count($attribList);
	
	/*
	  The part of code that separates out the attributes comes 
      from code written for a another project with similar needs.
      It was contributed to the other project by Kyle Smith.  
     As with the other project his code here is a must in using
     attributes in the tag.

    A small modification was to add the code to axiom.
   The $pattern variable contains m for the [m] tag in axiom
   where it was not needed or wanted in the other project.
   */

      $pattern = "'\[\m .*?\]|\[/.*\]'si";
	  preg_match_all($pattern,$content,$tagList);
	  $exprList = preg_split($pattern,$content,-1);
	  $Find = array( "/\[/","/\]/","/\//");
	  $Destroy = array ("","","");
	  //splits attributes out of tag
	  foreach ($tagList as $key => $value) {
      $init_val_count = count($value);
	  	for ($i = 0; $i < count($value); $i++) {
			$tagList[$i] = preg_replace($Find, $Destory,$value[$i]);
			$tagAttrib[$i] = preg_split('/ /', $tagList[$i], -1);
			}//end for
		}//end foreach
		$j = 0;
		for ($i = 0; $i < count($tagAttrib); $i+=2) {
					$tagAttribs[$j] = $tagAttrib[$i];
					$j++;
					}

		for($key = 0; $key < count($tagAttribs); $key++)
		   for($i = 0; $i < count($tagAttribs[$key]); $i++)
				$tagAttribs[$key][$i] = preg_split('/=/',$tagAttribs[$key][$i],-1);
	
	// This ends the code by Kyle Smith.

    /*
     Use the preg_replace to make the tag only [m] with no
     attributes. The values for the attributes are in the 
     arrays the tags must be cleaned up of all attributes
     as PHPMathPublisher will error if any attributes are in
     the tags.
   */
	$content = preg_replace("'\[m .*?\]'", "[m]", $content);
	
	$tacnt = count($tagAttribs[0]);
	
	for($i = 0; $i < $tacnt; $i++) {
		for($j = 0; $j < $attribListCnt; $j++) {
			
			if($tagAttribs[0][$i][0] == $attribList[$j]) {
				
				switch ($attribList[$j]) {
					case 'size': $math_size = ', ' . $tagAttribs[0][$i][1];
					                   break;
									   
				} // switch $attrib[$j] {
				
			} // if($tagAttribs[0][$i][0] == $attribList[$j]) {
			
		} // for($j = 0; $j < $attribListCnt; $j++) {
	} // for($i = 0; $i < $tacnt; $i++) {

	$content = preg_replace('#\[m\](.*?)\[/m\]#sie', 'wpmathfilter(\'\\1\'' . $math_size . ');', $content);
	return $content;
}

// Register this filter before Markdown (index=6)
add_filter('the_content', 'to_phpmath', 5);

?>