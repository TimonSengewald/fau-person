<?php


 if(!function_exists('fau_person')) {   
    function fau_person( $atts, $content = null) {
            extract(shortcode_atts(array(
                    "slug" => 'slug',
                    "id" => FALSE,
                    "showlink" => FALSE,
                    "showfax" => FALSE,
                    "showwebsite" => FALSE,
                    "showaddress" => FALSE,
                    "showroom" => FALSE,
                    "showdescription" => FALSE,
                    "showthumb" => FALSE,
                    "showpubs" => FALSE,
                    "showoffice" => FALSE,
                    "showtitle" => TRUE,
                    "showsuffix" => TRUE,
                    "showposition" => TRUE,
                    "showinstitution" => TRUE,
                    "showmail" => TRUE,
                    "showtelefon" => TRUE,
                    "extended" => FALSE,
                    ), $atts));
 

            if(!empty($id)) {
                   $slug = get_post($id)->post_title;
            }
            $posts = get_posts(array('name' => $slug, 'post_type' => 'person', 'post_status' => 'publish'));
            if ($posts) {
                $post = $posts[0];
                $id = $post->ID;		    
                return fau_person_markup($id, $extended, $showlink, $showfax, $showwebsite, $showaddress, $showroom, $showdescription, $showthumb, $showpubs, $showoffice, $showtitle, $showsuffix, $showposition, $showinstitution, $showmail, $showtelefon);
            } else {
                return sprintf(__('Es konnte kein Kontakteintrag mit der angegebenen ID %s gefunden werden.', FAU_PERSON_TEXTDOMAIN), $slug);
            }
    }
 }

 if(!function_exists('fau_persons')) {
    function fau_persons( $atts, $content = null) {
            extract(shortcode_atts(array(
                    "category" => 'category',
                    "showlink" => FALSE,
                    "showfax" => FALSE,
                    "showwebsite" => FALSE,
                    "showaddress" => FALSE,
                    "showroom" => FALSE,
                    "showdescription" => FALSE,
                    "showthumb" => FALSE,
                    "showpubs" => FALSE,
                    "showoffice" => FALSE,
                    "showtitle" => TRUE,
                    "showsuffix" => TRUE,
                    "showposition" => TRUE,
                    "showinstitution" => TRUE,
                    "showmail" => TRUE,
                    "showtelefon" => TRUE,
                    "extended" => FALSE
                    ), $atts));

            $category = get_term_by('slug', $category, 'persons_category');

            $posts = get_posts(array('post_type' => 'person', 'post_status' => 'publish', 'numberposts' => 1000, 'orderby' => 'title', 'order' => 'ASC', 'tax_query' => array(
                    array(
                            'taxonomy' => 'persons_category',
                            'field' => 'id', // can be slug or id - a CPT-onomy term's ID is the same as its post ID
                            'terms' => $category->term_id
                            )
                    ), 'suppress_filters' => false));

            $content = '';

            foreach($posts as $post)
            {
                    $content .= fau_person_markup($post->ID, $extended, $showlink, $showfax, $showwebsite, $showaddress, $showroom, $showdescription, $showthumb, $showpubs, $showoffice, $showtitle, $showsuffix, $showposition, $showinstitution, $showmail, $showtelefon);
            }

            return $content;
    }
 }

 if(!function_exists('fau_person_markup')) {
    function fau_person_markup($id, $extended, $showlink, $showfax, $showwebsite, $showaddress, $showroom, $showdescription, $showthumb, $showpubs, $showoffice, $showtitle, $showsuffix, $showposition, $showinstitution, $showmail, $showtelefon)
    {
            $honorificPrefix = get_post_meta($id, 'fau_person_honorificPrefix', true);
            $givenName = get_post_meta($id, 'fau_person_givenName', true);
            $familyName = get_post_meta($id, 'fau_person_familyName', true);
            $honorificSuffix = get_post_meta($id, 'fau_person_honorificSuffix', true);
            $jobTitle = get_post_meta($id, 'fau_person_jobTitle', true);
            $worksFor = get_post_meta($id, 'fau_person_worksFor', true);
            $telephone = get_post_meta($id, 'fau_person_telephone', true);
            $faxNumber = get_post_meta($id, 'fau_person_faxNumber', true);
            $email = get_post_meta($id, 'fau_person_email', true);
            $url = get_post_meta($id, 'fau_person_url', true);
            $streetAddress = get_post_meta($id, 'fau_person_streetAddress', true);
            $postalCode = get_post_meta($id, 'fau_person_postalCode', true);
            $addressLocality = get_post_meta($id, 'fau_person_addressLocality', true);
            $addressCountry = get_post_meta($id, 'fau_person_addressCountry', true);
            $workLocation = get_post_meta($id, 'fau_person_workLocation', true);
            $hoursAvailable = get_post_meta($id, 'fau_person_hoursAvailable', true);
            $pubs = get_post_meta($id, 'fau_person_pubs', true);
            $freitext = get_post_meta($id, 'fau_person_freitext', true);
            $link = get_post_meta($id, 'fau_person_link', true);
            
                                                            //ACHTUNG: vorher css person-info-address (war Textarea bei FAU)!!!
                                                if($streetAddress || $postalCode || $addressLocality || $addressCountry) {
                                                    $contactpoint = '<li class="person-info-address"><span class="screen-reader-text">'.__('Adresse',FAU_PERSON_TEXTDOMAIN).': </span><br>';    
                                                
                                                    if($streetAddress)          $contactpoint .= '<span class="person-info-street" itemprop="streetAddress">'.$streetAddress.'</span>';
                                                    if($streetAddress && ($postalCode || $addressLocality)) $contactpoint .= '<br>';
                                                    if($postalCode || $addressLocality) {
                                                        $contactpoint .= '<span class="person-info-city">';
                                                        if($postalCode)         $contactpoint .= '<span itemprop="postalCode">'.$postalCode.'</span> ';  
                                                        if($addressLocality)	$contactpoint .= '<span itemprop="addressLocality">'.$addressLocality.'</span>';
                                                        $contactpoint .= '</span>';
                                                    }
                                                    if(($streetAddress || $postalCode || $addressLocality) && $addressCountry)                    $contactpoint .= '<br>';
                                                    if($addressCountry)         $contactpoint .= '<span class="person-info-country" itemprop="addressCountry">'.$addressCountry.'</span></';
                                                    $contactpoint .= '</li>';                                                
                                                }
            
        
            $content = '<div class="person content-person" itemscope itemtype="http://schema.org/Person">';			
                    $content .= '<div class="row">';

                            if(has_post_thumbnail($id) && $showthumb)
                            {
                                    $content .= '<div class="span1 span-small" itemprop="image">';
                                            $content .= get_the_post_thumbnail($id, 'person-thumb-bigger');
                                    $content .= '</div>';
                            }

                            $content .= '<div class="span3">';
                                    $content .= '<h3>';
                                            if($showtitle && $honorificPrefix) 	$content .= $honorificPrefix . ' ';
                                            $content .= get_the_title($id);
                                            if($showsuffix && $honorificSuffix) 	$content .= ' '.$honorificSuffix;
                                    $content .= '</h3>';
                                    $content .= '<ul class="person-info">';
                                            if($showposition && $jobTitle) 				$content .= '<li class="person-info-position"><span class="screen-reader-text">'.__('Tätigkeit',FAU_PERSON_TEXTDOMAIN).': </span><strong><span itemprop="jobTitle">'.$jobTitle.'</span></strong></li>';
                                            if($showinstitution && $worksFor)			$content .= '<li class="person-info-institution"><span class="screen-reader-text">'.__('Einrichtung',FAU_PERSON_TEXTDOMAIN).': </span><span itemprop="worksFor">'.$worksFor.'</span></li>';
                                            if($showtelefon && $telephone)					$content .= '<li class="person-info-phone"><span class="screen-reader-text">'.__('Telefonnummer',FAU_PERSON_TEXTDOMAIN).': </span><span itemprop="telephone">'.$telephone.'</span></li>';
                                            if(($extended || $showfax) && $faxNumber)		$content .= '<li class="person-info-fax"><span class="screen-reader-text">'.__('Faxnummer',FAU_PERSON_TEXTDOMAIN).': </span><span itemprop="faxNumber">'.$faxNumber.'</span></li>';
                                            if($showmail && $email)					$content .= '<li class="person-info-email"><span class="screen-reader-text">'.__('E-Mail',FAU_PERSON_TEXTDOMAIN).': </span><a itemprop="email" href="mailto:'.strtolower($email).'">'.strtolower($email).'</a></li>';
                                            if(($extended || $showwebsite) && $url)	$content .= '<li class="person-info-www"><span class="screen-reader-text">'.__('Webseite',FAU_PERSON_TEXTDOMAIN).': </span><a itemprop="url" href="'.$url.'">'.$url.'</a></li>';
                                            if(($extended || $showaddress) && !empty($contactpoint)) {
                                                    $content .= $contactpoint;
                                            }
                                            if(($extended || $showroom) && $workLocation)		$content .= '<li class="person-info-room"><span class="screen-reader-text">' . __('Raum', FAU_PERSON_TEXTDOMAIN) .' </span><span itemprop="workLocation">'.$workLocation.'</span></li>';
                                            if($showoffice && $hoursAvailable)		$content .= '<li class="person-info-office"><span class="screen-reader-text">' . __('Sprechzeiten', FAU_PERSON_TEXTDOMAIN) .': </span><span itemprop="hoursAvailable">'.$hoursAvailable.'</span></li>';
                                            if($showpubs && $pubs)		$content .= '<li class="person-info-pubs"><span class="screen-reader-text">' . __('Publikationen', FAU_PERSON_TEXTDOMAIN) .': </span>'.$pubs.'</li>';                                            
                                            $content .= '</ul>';

                            $content .= '</div>';
                            $content .= '<div class="span3">';
                                    if(($extended || $showdescription) && $freitext)		$content .= '<div class="person-info-description">'.$freitext.'</div>';
                                    if($showlink && $link) {
                                            $content .= '<div class="person-info-more"><a title="' . sprintf(__('Weitere Informationen zu %s aufrufen', FAU_PERSON_TEXTDOMAIN), get_the_title($id)) . '" class="person-read-more" href="'.$link.'">';
                                            $content .= __('Mehr', FAU_PERSON_TEXTDOMAIN) . ' ›</a></div>';
                                    }

                            $content .= '</div>';
                    $content .= '</div>';

            $content .= '</div>';

            return $content;
    }
 }

   
?>