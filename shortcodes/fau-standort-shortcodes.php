<?php


 if(!function_exists('fau_standort')) {   
    function fau_standort( $atts, $content = null) {
            extract(shortcode_atts(array(
            "slug" => FALSE,
            "id" => FALSE,
            "showaddress" => TRUE,
            "showlist" => FALSE,
            "showthumb" => FALSE,
            //"format" => '',
            "show" => '', 
            "hide" => '',
                        ), $atts));
          
        $sidebar = '';
        $page = '';
        $list = '';
        if ( !empty( $format ) ) {         
            if( $format == 'sidebar' ) {
                $showsidebar = 1;
                $sidebar = 1;
                $showaddress = 0;
                $showdescription = 1;
                $showthumb = 1;
            }
            if( $format == 'full' || $format == 'page' )        $page = 1;
            if( $format == 'liste' ) {
                $list = 1;
                $showlist = 1;
            }
        }     
        //Wenn neue Felder dazukommen, hier die Anzeigeoptionen auch mit einstellen
        if (!empty($show)) {
            $show = explode(', ', $show);
            if( in_array( 'kurzbeschreibung', $show ) ) $showlist = 1;  
            if( in_array( 'adresse', $show ) )          $showaddress = 1;            
            if( in_array( 'bild', $show ) )             $showthumb = 1;
        }    
        if ( !empty( $hide ) ) {
            $hide = explode(', ', $hide);
            if( in_array( 'kurzbeschreibung', $hide ) ) $showlist = 0;
            if( in_array( 'adresse', $hide ) )          $showaddress = 0;            
            if( in_array( 'bild', $hide ) )             $showthumb = 0;         
        }
                

        if (empty($id)) {
            if (empty($slug)) {
                return '<p>' . sprintf(__('Bitte geben Sie den Titel oder die ID des Kontakteintrags an.', FAU_PERSON_TEXTDOMAIN), $slug) . '</p>';
            } else {
                $posts = get_posts(array('name' => $slug, 'post_type' => 'person', 'post_status' => 'publish'));
                if ($posts) {
                    $post = $posts[0];
                    $id = $post->ID;
                } else {
                    return '<p>' . sprintf(__('Es konnte kein Kontakteintrag mit dem angegebenen Titel %s gefunden werden. Versuchen Sie statt dessen die Angabe der ID des Kontakteintrags.', FAU_PERSON_TEXTDOMAIN), $slug) . '</p>';
                }
            }
        }

        if (!empty($id)) {

            $list_ids = explode(',', $id);
            if ( $shortlist ) {
                $liste = '<span class="person liste-person" itemscope itemtype="http://schema.org/Person">';
            } elseif ( $page ) {
                $liste = '';
            } elseif ( $list ) {
                $liste = '<ul class="person liste-person" itemscope itemtype="http://schema.org/Person">';
                $liste .= "\n";              
            } else {
                $liste = '<p>';
            }

            $number = count($list_ids);   
            $i = 1;
            foreach ($list_ids as $value) {
                $post = get_post($value);
                if ($post && $post->post_type == 'person') {
                    if ( $page ) {
                        $liste .= fau_person_page($value);
                    } elseif ( $shortlist ) {
                        $liste .= fau_person_shortlist($value, $showlist);
                        if( $i < $number )  $liste .= ", ";
                    } elseif ( $list ) {
                        $liste .= '<li class="person-info">'."\n";
                        $liste .= fau_person_shortlist($value, $showlist);
                        $content .= "</li>\n";
                    } elseif ( $sidebar ) {
                        $liste .= fau_person_sidebar($value, 0, $showlist, $showinstitution, $showabteilung, $showposition, $showtitle, $showsuffix, $showaddress, $showroom, $showtelefon, $showfax, $showmobile, $showmail, $showwebsite, $showlink, $showdescription, $showoffice, $showpubs, $showthumb);
                    } else {
                        $liste .= fau_person_markup($value, $extended, $showlink, $showfax, $showwebsite, $showaddress, $showroom, $showdescription, $showlist, $showsidebar, $showthumb, $showpubs, $showoffice, $showtitle, $showsuffix, $showposition, $showinstitution, $showabteilung, $showmail, $showtelefon, $showmobile);
                    }
                } else {
                    $liste .=  sprintf(__('Es konnte kein Kontakteintrag mit der angegebenen ID %s gefunden werden.', FAU_PERSON_TEXTDOMAIN), $value);
                    if( $i < $number )  $liste .= ", ";
                }
                $i++;
            }
            if ( $shortlist  ) {
                $liste .= "</span>";
            } elseif ( $list ) {
                $liste .= "</ul>\n";
            } elseif ( $page ) {
                $post = get_post( $id );
                if ( $post->post_content ) $content = wpautop($post->post_content);  
                $liste .= $content;
            } else {
                $liste .= "</p>\n";                
            } 
            return $liste;
            
        }
    }

}

if(!function_exists('fau_person_markup')) {

    function fau_person_markup($id, $extended, $showlink, $showfax, $showwebsite, $showaddress, $showroom, $showdescription, $showlist, $showsidebar, $showthumb, $showpubs, $showoffice, $showtitle, $showsuffix, $showposition, $showinstitution, $showabteilung, $showmail, $showtelefon, $showmobile) {
        $fields = sync_helper::get_fields( $id, get_post_meta($id, 'fau_person_univis_id', true), 0 );
        extract($fields);
        
	$type = get_post_meta($id, 'fau_person_typ', true);

        if( $link ) {
            $personlink = $link;
        } else {
            $personlink = get_permalink( $id );
        }
        
        if( get_post_field( 'post_excerpt', $id ) ) {
            $excerpt = get_post_field( 'post_excerpt', $id );                
        } else {
            $post = get_post( $id );
            if ( $post->post_content )      
                $excerpt = wp_trim_excerpt($post->post_content);
        }         
            
        if($streetAddress || $postalCode || $addressLocality || $addressCountry) {
            $contactpoint = '<li class="person-info-address"><span class="screen-reader-text">'.__('Adresse',FAU_PERSON_TEXTDOMAIN).': <br></span>';            
            if($streetAddress) {
                $contactpoint .= '<span class="person-info-street" itemprop="streetAddress">'.$streetAddress.'</span>';
                if( $postalCode || $addressLocality )  {
                    $contactpoint .= '<br>';
                } elseif( $addressCountry ) {
                    $contactpoint .= '<br>';
                }                    
            }
            if($postalCode || $addressLocality) {
                $contactpoint .= '<span class="person-info-city">';
                if($postalCode)             
                    $contactpoint .= '<span itemprop="postalCode">'.$postalCode.'</span> ';  
                if($addressLocality)	
                    $contactpoint .= '<span itemprop="addressLocality">'.$addressLocality.'</span>';
                $contactpoint .= '</span>';
                if( $addressCountry )       
                    $contactpoint .= '<br>';
            }                  
            if( $addressCountry )         
                $contactpoint .= '<span class="person-info-country" itemprop="addressCountry">'.$addressCountry.'</span>';
            $contactpoint .= '</li>';                                                
        }
        
        $fullname = '';
        if($showtitle && $honorificPrefix)                      
            $fullname .= '<span itemprop="honorificPrefix">' . $honorificPrefix . '</span> ';
        if($givenName || $familyName) {
                    if($givenName)          $fullname .= '<span itemprop="givenName">'.$givenName."</span> ";
                    if($familyName)         $fullname .= '<span itemprop="familyName">'.$familyName."</span>";
        } elseif( !empty( get_the_title($id) ) ) {                                                
            $fullname .= get_the_title($id);
        }
        if($showsuffix && $honorificSuffix)                     
            $fullname .= ', <span itemprop="honorificSuffix">' . $honorificSuffix . '</span>';
        
                    
        $content = '<div class="person content-person" itemscope itemtype="http://schema.org/Person">';			
        $content .= '<div class="row">';

        if($showthumb) {
            $content .= '<div class="span1 span-small" itemprop="image">';	
            $content .= '<a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" href="' . $personlink . '">';
            if (has_post_thumbnail($id)) {
                $content .= get_the_post_thumbnail($id, 'person-thumb-bigger');
            } else {
		if ($type == 'realmale') {
                    $bild =  plugin_dir_url( __FILE__ ) .'../images/platzhalter-mann.png';   
		} elseif ($type == 'realfemale') {
                    $bild = plugin_dir_url( __FILE__ ) .'../images/platzhalter-frau.png';
                } elseif ($type == 'einrichtung') {
                    $bild = plugin_dir_url( __FILE__ ) .'../images/platzhalter-organisation.png';
                } else {
                    $bild = plugin_dir_url( __FILE__ ) .'../images/platzhalter-unisex.png';
                }				    
		if ($bild) 
                    $content .=  '<img src="'.$bild.'" width="90" height="120" alt="">';
            }
            $content .= '</a>';
            $content .= '</div>';
        }
        $content .= '<div class="span3">';
        $content .= '<h3>';        
        $content .= '<a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" href="' . $personlink . '">' . $fullname . '</a>';
        $content .= '</h3>';
        $content .= '<ul class="person-info">';
        if ($showposition && $jobTitle)
            $content .= '<li class="person-info-position"><span class="screen-reader-text">' . __('Tätigkeit', FAU_PERSON_TEXTDOMAIN) . ': </span><strong><span itemprop="jobTitle">' . $jobTitle . '</span></strong></li>';
        if ($showinstitution && $worksFor)
            $content .= '<li class="person-info-institution"><span class="screen-reader-text">' . __('Organisation', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="worksFor">' . $worksFor . '</span></li>';
        if ($showabteilung && $department)
            $content .= '<li class="person-info-abteilung"><span class="screen-reader-text">' . __('Abteilung', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="department">' . $department . '</span></li>';
        if ($showtelefon && $telephone)
            $content .= '<li class="person-info-phone"><span class="screen-reader-text">' . __('Telefonnummer', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="telephone">' . $telephone . '</span></li>';
        if (($extended || $showfax) && $faxNumber)
            $content .= '<li class="person-info-fax"><span class="screen-reader-text">' . __('Faxnummer', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="faxNumber">' . $faxNumber . '</span></li>';
        if ($showmail && $email)
            $content .= '<li class="person-info-email"><span class="screen-reader-text">' . __('E-Mail', FAU_PERSON_TEXTDOMAIN) . ': </span><a itemprop="email" href="mailto:' . strtolower($email) . '">' . strtolower($email) . '</a></li>';
        if (($extended || $showwebsite) && $url)
            $content .= '<li class="person-info-www"><span class="screen-reader-text">' . __('Webseite', FAU_PERSON_TEXTDOMAIN) . ': </span><a itemprop="url" href="' . $url . '">' . $url . '</a></li>';
        if (($extended || $showaddress) && !empty($contactpoint)) 
            $content .= $contactpoint;
        if (($extended || $showroom) && $workLocation)
            $content .= '<li class="person-info-room"><span class="screen-reader-text">' . __('Raum', FAU_PERSON_TEXTDOMAIN) . ' </span><span itemprop="workLocation">' . $workLocation . '</span></li>';
        if ($showoffice && $hoursAvailable)
            $content .= '<li class="person-info-office"><span class="screen-reader-text">' . __('Sprechzeiten', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="hoursAvailable">' . $hoursAvailable . '</span></li>';
        if ($showpubs && $pubs)
            $content .= '<li class="person-info-pubs"><span class="screen-reader-text">' . __('Publikationen', FAU_PERSON_TEXTDOMAIN) . ': </span>' . $pubs . '</li>';
        $content .= '</ul>';

        $content .= '</div>';
        if (($showlist && $excerpt) || (($showsidebar || $extended) && $description) || ($showlink && $personlink)) {
            $content .= '<div class="span3">';
            if ($showlist && $excerpt)
                $content .= '<div class="person-info-description"><p>' . $excerpt . '</p></div>';
            if (($extended || $showsidebar) && $description)
                $content .= '<div class="person-info-description"><span class="screen-reader-text">' . __('Beschreibung', FAU_PERSON_TEXTDOMAIN) . ': </span>' . $description . '</div>';
            if ($showlink && $personlink) {
                $content .= '<div class="person-info-more"><a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" class="person-read-more" href="' . $personlink . '">';
                $content .= __('Mehr', FAU_PERSON_TEXTDOMAIN) . ' ›</a></div>';
            }
            $content .= '</div>';
        }
        $content .= '</div>';
        $content .= '</div>';
        return $content;
    }

}

 if(!function_exists('fau_standort_page')) {
    function fau_standort_page($id) {
 
     	$content = '<div class="person" itemscope itemtype="http://schema.org/Person">';
        
        $fields = standort_sync_helper::get_fields($id, get_post_meta($id, 'fau_person_standort_id', true), 0);
        extract($fields);
        
        $fullname = '';
        if( !empty( get_the_title($id) ) ) {                                                
            $fullname .= get_the_title($id);
        }
        $content .= '<h2 itemprop="name">' . $fullname . '</h2>';
        if ($streetAddress || $postalCode || $addressLocality || $addressCountry) {
            $contactpoint = '<li class="person-info-address"><span class="screen-reader-text">' . __('Adresse', FAU_PERSON_TEXTDOMAIN) . ': <br></span>';
            if ($streetAddress) {
                $contactpoint .= '<span class="person-info-street" itemprop="streetAddress">' . $streetAddress . '</span>';
                if ($postalCode || $addressLocality) {
                    $contactpoint .= '<br>';
                } elseif ($addressCountry) {
                    $contactpoint .= '<br>';
                }
            }
            if ($postalCode || $addressLocality) {
                $contactpoint .= '<span class="person-info-city">';
                if ($postalCode)
                    $contactpoint .= '<span itemprop="postalCode">' . $postalCode . '</span> ';
                if ($addressLocality)
                    $contactpoint .= '<span itemprop="addressLocality">' . $addressLocality . '</span>';
                $contactpoint .= '</span>';
                if ($addressCountry)
                    $contactpoint .= '<br>';
            }
            if ($addressCountry)
                $contactpoint .= '<span class="person-info-country" itemprop="addressCountry">' . $addressCountry . '</span></';
            $contactpoint .= '</li>';
        }
        //$content = '';
        
        $post = get_post($id);
        if (has_post_thumbnail($id)) {
            $content .= '<div itemprop="image" class="alignright">';
            // $content .= get_the_post_thumbnail($id, 'post');	    
            $content .= get_the_post_thumbnail($id, 'person-thumb-page');
            $content .= '</div>';
        }
        $content .= '<ul class="person-info">';
        if (!empty($contactpoint)) {
            $content .= $contactpoint;
        }
        $content .= '</ul>';
        $content .= '</div>';

        return $content;
    } 
 }    
  
if(!function_exists('fau_standort_shortlist')) {
    function fau_standort_shortlist($id, $showlist) {	
        
        $fields = sync_helper::get_fields($id, get_post_meta($id, 'fau_person_univis_id', true), 0);
        extract($fields);
        
            if( get_post_field( 'post_excerpt', $id ) ) {
                $excerpt = get_post_field( 'post_excerpt', $id );                
            } else {
                $post = get_post( $id );
                if ( $post->post_content )      $excerpt = wp_trim_excerpt($post->post_content);
            }
            
            if( $link ) {
                $personlink = $link;
            } else {
                $personlink = get_permalink( $id );
            }
            $content = '';			           
		$fullname = '';
                if (!empty(get_the_title($id) ) ) {
                    $fullname .= get_the_title($id);
                }
                $content .= '<span class="person-info">';
                $content .= '<a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" href="' . $personlink . '">' . $fullname . '</a>';
                if( $showlist && $excerpt )                                  $content .= "<br>".$excerpt;    
                $content .= '</span>';
            return $content;
    }
 }
 
if(!function_exists('fau_standort_sidebar')) {
    function fau_standort_sidebar($id, $title, $showlist=0, $showinstitution=0, $showabteilung=0, $showposition=0, $showtitle=0, $showsuffix=0, $showaddress=0, $showroom=0, $showtelefon=0, $showfax=0, $showmobile=0, $showmail=0, $showwebsite=0, $showlink=0, $showdescription=0, $showoffice=0, $showpubs=0, $showthumb=0) {
            if (!empty($id)) {
            $post = get_post($id);

            $fields = sync_helper::get_fields($id, get_post_meta($id, 'fau_person_univis_id', true), 0);
            extract($fields);

            if( $link ) {
                $personlink = $link;
            } else {
                $personlink = get_permalink( $id );
            }
            
            if( $showaddress ) {
                if ($streetAddress || $postalCode || $addressLocality || $addressCountry) {
                    $contactpoint = '<li class="person-info-address"><span class="screen-reader-text">' . __('Adresse', FAU_PERSON_TEXTDOMAIN) . ': <br></span>';
                    if ($streetAddress) {
                        $contactpoint .= '<span class="person-info-street" itemprop="streetAddress">' . $streetAddress . '</span>';
                        if ($postalCode || $addressLocality) {
                            $contactpoint .= '<br>';
                        } elseif ($addressCountry) {
                            $contactpoint .= '<br>';
                        }
                    }
                    if ($postalCode || $addressLocality) {
                        $contactpoint .= '<span class="person-info-city">';
                        if ($postalCode)
                            $contactpoint .= '<span itemprop="postalCode">' . $postalCode . '</span> ';
                        if ($addressLocality)
                            $contactpoint .= '<span itemprop="addressLocality">' . $addressLocality . '</span>';
                        $contactpoint .= '</span>';
                        if ($addressCountry)
                            $contactpoint .= '<br>';
                    }
                    if ($addressCountry)
                        $contactpoint .= '<span class="person-info-country" itemprop="addressCountry">' . $addressCountry . '</span>';
                    $contactpoint .= '</li>';
                }
            }

            $fullname = '';
            if ($honorificPrefix && $showtitle)           $fullname .= '<span itemprop="honorificPrefix">' . $honorificPrefix . '</span> ';
            if($givenName || $familyName) {
                if($givenName)              $fullname .= '<span itemprop="givenName">'.$givenName."</span> ";
                if($familyName)             $fullname .= '<span itemprop="familyName">'.$familyName."</span>";
            } elseif( !empty( get_the_title($id) ) ) {                                                
                $fullname .= get_the_title($id);
            }
            if ($honorificSuffix && $showsuffix)           $fullname .= ', <span itemprop="honorificSuffix">' . $honorificSuffix . '</span>';
            
            $content = '<div class="person" itemscope itemtype="http://schema.org/Person">';
            
            if (!empty($title)) 
                $content .= '<h2 class="small">' . $title . '</h2>';

            $content .= '<div class="row">';

            if (has_post_thumbnail($id) && $showthumb) {
                $content .= '<div class="span1" itemprop="image">';
                $content .= '<a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" href="' . $personlink . '">';
                $content .= get_the_post_thumbnail($id, 'person-thumb');
                $content .= '</a>';
                $content .= '</div>';
            }

            $content .= '<div class="span3">';
            $content .= '<h3>';
            $content .= '<a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" href="' . $personlink . '">' . $fullname . '</a>';
            $content .= '</h3>';
            $content .= '<ul class="person-info">';
            if ($jobTitle && $showposition)
                $content .= '<li class="person-info-position"><span class="screen-reader-text">' . __('Tätigkeit', FAU_PERSON_TEXTDOMAIN) . ': </span><strong><span itemprop="jobTitle">' . $jobTitle . '</span></strong></li>';
            if ($worksFor && $showinstitution)
                $content .= '<li class="person-info-institution"><span class="screen-reader-text">' . __('Organisation', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="worksFor">' . $worksFor . '</span></li>';
            if ($department && $showabteilung)
                $content .= '<li class="person-info-abteilung"><span class="screen-reader-text">' . __('Abteilung', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="department">' . $department . '</span></li>';
            if ($telephone && $showtelefon)
                $content .= '<li class="person-info-phone"><span class="screen-reader-text">' . __('Telefonnummer', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="telephone">' . $telephone . '</span></li>';
            if ($faxNumber && $showfax)
                $content .= '<li class="person-info-fax"><span class="screen-reader-text">' . __('Faxnummer', FAU_PERSON_TEXTDOMAIN) . ': </span><span itemprop="faxNumber">' . $faxNumber . '</span></li>';
            if ($email && $showmail)
                $content .= '<li class="person-info-email"><span class="screen-reader-text">' . __('E-Mail', FAU_PERSON_TEXTDOMAIN) . ': </span><a itemprop="email" href="mailto:' . strtolower($email) . '">' . strtolower($email) . '</a></li>';
            if ($url && $showwebsite)
                $content .= '<li class="person-info-www"><span class="screen-reader-text">' . __('Webseite', FAU_PERSON_TEXTDOMAIN) . ': </span><a itemprop="url" href="' . $url . '">' . $url . '</a></li>';
            if (!empty($contactpoint))
                $content .= $contactpoint;
            if ($workLocation && $showoffice)
                $content .= '<li class="person-info-room"><span class="screen-reader-text">' . __('Raum', FAU_PERSON_TEXTDOMAIN) . ' </span><span itemprop="workLocation">' . $workLocation . '</span></li>';
            $content .= '</ul>';
            if ($description && $showdescription)
                $content .= '<div class="person-info-description"><span class="screen-reader-text">' . __('Beschreibung', FAU_PERSON_TEXTDOMAIN) . ': </span>' . $description . '</div>';
            $content .= '</div>';
            $content .= '</div>';

            $content .= '</div>';
        }
        return $content;

    }
}