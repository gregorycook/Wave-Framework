<?php/** * Wave Framework <http://www.waveframework.com> * API Documentation Generator * * This script lists all controllers and their methods, as well as all API profiles that can use  * these methods. This is meant for an overview of API commands for documentation purposes or  * otherwise. It shows all API commands based on what their 'www-command' value should be as well  * as displays comments that have been written next to these methods. It is also possible to show  * commands for only specific API profile by defining GET variable 'profile'. * * @package    Tools * @author     Kristo Vaher <kristo@waher.net> * @copyright  Copyright (c) 2012, Kristo Vaher * @license    GNU Lesser General Public License Version 3 * @tutorial   /doc/pages/guide_tools.htm * @since      2.2.1 * @version    3.1.4 */// This initializes tools and authenticationrequire('.'.DIRECTORY_SEPARATOR.'tools_autoload.php');// Log is printed out in plain text formatheader('Content-Type: text/html;charset=utf-8');?><!DOCTYPE html><html lang="en">	<head>		<title>API Info</title>		<meta charset="utf-8">		<meta name="viewport" content="width=device-width"/> 		<link type="text/css" href="style.css" rel="stylesheet" media="all"/>		<link rel="icon" href="../favicon.ico" type="image/x-icon"/>		<link rel="icon" href="../favicon.ico" type="image/vnd.microsoft.icon"/>		<meta content="noindex,nocache,nofollow,noarchive,noimageindex,nosnippet" name="robots"/>		<meta http-equiv="cache-control" content="no-cache"/>		<meta http-equiv="pragma" content="no-cache"/>		<meta http-equiv="expires" content="0"/>	</head>	<body>		<?php		// If API commands for specific profile only are returned		if(isset($_GET['profile'])){			$apiProfile=$_GET['profile'];		} else {			$apiProfile='*';		}		// Factory class is required by all of custom-classes		$apiProfiles=parse_ini_file('../resources/api.profiles.ini',true);		// Getting list of controller files		$controllerFiles=fileIndex('../controllers/','files');        // Methods and their comments are stored here        $methodComments=array();		// If controllers were found		if($controllerFiles && !empty($controllerFiles)){						// Looping over controllers and finding suitable ones			foreach($controllerFiles as $controllerFile){				// Controller file name will be checked for controller validity				$controllerFileInfo=explode('.',$controllerFile);				// Simple file-name check				$extension=array_pop($controllerFileInfo);				$className=array_pop($controllerFileInfo);								if($extension=='php' && array_pop($controllerFileInfo)=='/controllers/controller'){									// Getting tokens from the file					$tokens=token_get_all(file_get_contents($controllerFile));					// Temporary comment gatherer					$tmpComments=array();					// This is used to check what level token is at					$gatherComments=false;					// This is used to double-check, making sure that the same token is not detected twice in a row					$prevToken=0;					$prevPrevToken=0;										// Looping over tokens					foreach($tokens as $token){						// Ignoring single matches						if(!is_string($token)){													// Testing based on values							if($token[0]==T_COMMENT || $token[0]==T_DOC_COMMENT){								// If token is a comment then it is added to comment array								$tmpComments[]=$token[1];							} elseif($token[0]==T_FUNCTION && $prevPrevToken!=T_PROTECTED && $prevPrevToken!=T_PRIVATE){								// Raising stage if function key is detected								$gatherComments=true;							} elseif($token[0]==T_STRING && $gatherComments){								// If token is function name that is not in illegal methods list, it is added to functions array								if(strpos($token[1],'WWW_')===false){									$methodComments[$className.'-'.strtolower(str_replace('_','-',$token[1]))]=$tmpComments;								}								// Comments are cleared for next method or token								$tmpComments=array();								$gatherComments=false;							} else if($token[0]!=T_WHITESPACE && $token[0]!=T_PUBLIC){								// Since token is not whitespace or is not public, token is cleared								$tmpComments=array();								$gatherComments=false;							}														// Assigning token as previous							$prevPrevToken=$prevToken;							$prevToken=$token[0];													}					}									}							}					}		// Header		echo '<h1>API Documentation</h1>';		echo '<h4 class="highlight">';		foreach($softwareVersions as $software=>$version){			// Adding version numbers			echo '<b>'.$software.'</b> ('.$version.') ';		}		echo '</h4>';			echo '<h2>Reference</h2>';		echo '<div class="border box">';			echo '<div class="box">';				echo '<span style="font-weight:bold;">{www-command value}</span><br/>';				if($apiProfile=='*'){					echo '<span style="font-style:italic; font-size:11px;">Allowed API profiles: {comma-separated list of API profiles that can use this command, if any}<br/>';				}				echo '<p style="font-style:italic;font-size:11px;padding:5px;margin:0px;">';					echo '{comments and description, if any}';				echo '</p>';			echo '</div>';			echo '<div class="disabled italic box">';				echo '<span style="font-weight:bold;">{Internal-only API command}</span><br/>';				echo '<p style="font-style:italic;font-size:11px;padding:5px;margin:0px;">';					echo '{comments and description, if any. Note that API command is considered internal only if no API profile has access to it.}';				echo '</p>';			echo '</div>';		echo '</div>';		// Title for API commands		if($apiProfile!='*'){			echo '<h2>API commands for <b>'.$apiProfile.'</b></h2>';			echo '<p>Show commands for <a href="api-info.php">all profiles</a></p>';		} else {			echo '<h2>API commands for all profiles</h2>';			echo '<p>Show only commands for specific profile: ';			$links=array();			// Looping over each API profile			foreach($apiProfiles as $key=>$profile){				$links[]='<a href="api-info.php?profile='.$key.'">'.$key.'</a>';			}			echo implode(', ',$links);			echo '</p>';		}        // Last controller data is stored here        $lastController='';				// Looping over found methods		foreach($methodComments as $command=>$comments){					// Getting current controller name			$tmp=explode('-',$command);			$controller=array_shift($tmp);			// Displaying controller-header, if new controller			if($controller!=$lastController){				echo '<h3 class="highlight">Controller: '.$controller.'</h3>';			}			$lastController=$controller;						// This array stores allowed profiles for current method			$allowedProfiles=array();			// Looping over each API profile			foreach($apiProfiles as $key=>$profile){				$commands=explode(',',$profile['commands']);				if(isset($profile['commands']) && ((in_array('*',$commands) && !in_array('!'.$command,$commands)) || (!in_array('*',$commands) && in_array($command,$commands)))){					$allowedProfiles[]=$key;				}			}						// Displaying information if all API data is shown or if selected profile is listed as allowed for that command			if($apiProfile=='*' || in_array($apiProfile,$allowedProfiles)){				if(empty($allowedProfiles)){					echo '<div class="disabled italic box">';				} else {					echo '<div class="box">';				}					// printing out main information					echo '<span class="bold">'.$command.'</span><br/>';					if($apiProfile=='*' && !empty($allowedProfiles)){						echo '<span class="small italic">Allowed API profiles:</span> '.implode(',',$allowedProfiles).'<br/>';					}					// Printing out comment information, if exists					if(!empty($comments)){						echo '<p class="italic">';						foreach($comments as $comment){							// Stripping comment tags							$commentLines=explode("\n",$comment);							foreach($commentLines as $key=>$c){								$c=trim($c);								if(!in_array($c,array('*','//','/**','/*','*/'))){									if(isset($c[0],$c[1]) && $c[0]=='*' && $c[1]==' '){										echo substr($c,2);									} elseif(isset($c[0],$c[1],$c[2]) && $c[0]=='/' && $c[1]=='/' && $c[2]==' '){										echo substr($c,3);									} else {										echo $c;									}									echo '<br/>';								} elseif($key>0){									echo '<br/>';								}							}						}						echo '</p>';					}				echo '</div>';			}					}				// Footer		echo '<p class="footer small bold">Generated at '.date('d.m.Y h:i').' GMT '.date('P').' for '.$_SERVER['HTTP_HOST'].'</p>';					?>	</body></html>