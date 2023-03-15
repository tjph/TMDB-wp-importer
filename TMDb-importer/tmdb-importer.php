<?php
/**
 * @package TMDb importer
 * @version 1.6
 */
/*
Plugin Name: TMDb importer
Plugin URI: http://wordpress.org/plugins/
Description: This imports movies in a custom post type
Author: Tom H
Version: 1.0
*/

//opcache_reset();


/* lets define some globals */
$siteurl = get_option('siteurl');
$API_KEY = YOUR_TMDB_API_KEY;


/* adding a menu item to the wp admin */
add_action('admin_menu','tmdbi_menu',3);
function tmdbi_menu() { 
    add_menu_page(
        "VMA Import",
        "VMA Import",
        "publish_posts",
        "movie_importer",
        "movie_importer_page",
        "",
        4
    ); 
}

/* this is the main plugin page */
function movie_importer_page()
{
    include 'importer-home.php';
}

add_action( 'init', 'tmdb_imp', 12 );
add_action( 'init', 'tmdb_imp_uniq', 12 );


add_action( 'init', 'tmdb_init', 10);

function tmdb_init() {
        // default post type
        register_post_type( 'movie', apply_filters( 'movie_post_type_args', array(
            'labels' => array(
                'name' => _x( 'Movies', 'post type general name' ),
                'singular_name' => _x( 'Movie', 'post type singular name' ),
                'add_new' => _x( 'Add New', 'movie' ),
                'add_new_item' => __( 'Add New Movie' ),
                'edit_item' => __( 'Edit Movie' ),
                'new_item' => __( 'New Movie' ),
                'all_items' => __( 'All Movies' ),
                'view_item' => __( 'View Movie' ),
                'search_items' => __( 'Search Movies' ),
                'not_found' =>  __( 'No movies found' ),
                'not_found_in_trash' => __( 'No movies found in Trash' ),
                'parent_item_colon' => '',
                'menu_name' => __( 'Movies' )
                ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'movies/%post_id%', 'with_front' => false ), // get_option( 'movie_slug', 'movies' ),
            'capability_type' => 'post',
            'has_archive' => get_option( 'movie_slug', 'movies' ),
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array( 'title', 'editor', 'movie', 'thumbnail', 'excerpt', 'comments', 'custom-fields' )
        ) ) );

        // taxonomies
        //  - genre
        //  - actor
        //  - director
        //  - author
        //  - certification

        // Genres
        $labels = array(
          'name' => _x( 'Genres', 'taxonomy general name' ),
          'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search Genres' ),
          'popular_items' => __( 'Popular Genres' ),
          'all_items' => __( 'All Genres' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit Genre' ),
          'update_item' => __( 'Update Genre' ),
          'add_new_item' => __( 'Add New Genre' ),
          'new_item_name' => __( 'New Genre Name' ),
          'separate_items_with_commas' => __( 'Separate genres with commas' ),
          'add_or_remove_items' => __( 'Add or remove genre' ),
          'choose_from_most_used' => __( 'Choose from the most used genres' ),
          'menu_name' => __( 'Genres' ),
        );

        register_taxonomy( 'movie_genre', 'movie', array(
          'hierarchical' => false,
          'labels' => $labels,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'genres', 'with_front' => false ),
        ) );

        // Actors
        $labelsb = array(
          'name' => _x( 'Actors', 'taxonomy general name' ),
          'singular_name' => _x( 'Actor', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search Actors' ),
          'popular_items' => __( 'Popular Actors' ),
          'all_items' => __( 'All Actors' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit Actor' ),
          'update_item' => __( 'Update Actor' ),
          'add_new_item' => __( 'Add New Actor' ),
          'new_item_name' => __( 'New Actor Name' ),
          'separate_items_with_commas' => __( 'Separate actors with commas' ),
          'add_or_remove_items' => __( 'Add or remove actor' ),
          'choose_from_most_used' => __( 'Choose from the most used actors' ),
          'menu_name' => __( 'Actors' ),
        );

        register_taxonomy( 'movie_actor', 'movie', array(
          'hierarchical' => false,
          'labels' => $labelsb,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'actors', 'with_front' => false ),
        ) );

        // Directors
        $labelsc = array(
          'name' => _x( 'Directors', 'taxonomy general name' ),
          'singular_name' => _x( 'Director', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search Directors' ),
          'popular_items' => __( 'Popular Directors' ),
          'all_items' => __( 'All Directors' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit Director' ),
          'update_item' => __( 'Update Director' ),
          'add_new_item' => __( 'Add New Director' ),
          'new_item_name' => __( 'New Director Name' ),
          'separate_items_with_commas' => __( 'Separate directors with commas' ),
          'add_or_remove_items' => __( 'Add or remove director' ),
          'choose_from_most_used' => __( 'Choose from the most used directors' ),
          'menu_name' => __( 'Directors' ),
        );

        register_taxonomy( 'movie_director', 'movie', array(
          'hierarchical' => false,
          'labels' => $labelsc,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'directors', 'with_front' => false ),
        ) );

        // Authors
        $labelsd = array(
          'name' => _x( 'Writers', 'taxonomy general name' ),
          'singular_name' => _x( 'Writer', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search writers' ),
          'popular_items' => __( 'Popular writers' ),
          'all_items' => __( 'All writers' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit writer' ),
          'update_item' => __( 'Update writer' ),
          'add_new_item' => __( 'Add New writer' ),
          'new_item_name' => __( 'New writer Name' ),
          'separate_items_with_commas' => __( 'Separate writers with commas' ),
          'add_or_remove_items' => __( 'Add or remove writer' ),
          'choose_from_most_used' => __( 'Choose from the most used writers' ),
          'menu_name' => __( 'Writers' ),
        );

        register_taxonomy( 'movie_writer', 'movie', array(
          'hierarchical' => false,
          'labels' => $labelsd,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'writers', 'with_front' => false ),
        ) );

        // Certification
        $labelse = array(
          'name' => _x( 'Certificate', 'taxonomy general name' ),
          'singular_name' => _x( 'Certificate', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search Certifications' ),
          'popular_items' => __( 'Popular Certifications' ),
          'all_items' => __( 'All Certifications' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit Certification' ),
          'update_item' => __( 'Update Certification' ),
          'add_new_item' => __( 'Add New Certification' ),
          'new_item_name' => __( 'New Certification Name' ),
          'separate_items_with_commas' => __( 'Separate certifications with commas' ),
          'add_or_remove_items' => __( 'Add or remove certification' ),
          'choose_from_most_used' => __( 'Choose from the most used certificates' ),
          'menu_name' => __( 'Certificate' ),
        );

        register_taxonomy( 'movie_certificate', 'movie', array(
          'hierarchical' => false,
          'labels' => $labelse,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'certificate', 'with_front' => false ),
        ) );



        // Production countries
        $labelsf = array(
          'name' => _x( 'Country', 'taxonomy general name' ),
          'singular_name' => _x( 'Country', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search Countries' ),
          'popular_items' => __( 'Popular Countries' ),
          'all_items' => __( 'All Countries' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit Country' ),
          'update_item' => __( 'Update Country' ),
          'add_new_item' => __( 'Add New Country' ),
          'new_item_name' => __( 'New Country Name' ),
          'separate_items_with_commas' => __( 'Separate countries with commas' ),
          'add_or_remove_items' => __( 'Add or remove country' ),
          'choose_from_most_used' => __( 'Choose from the most used countries' ),
          'menu_name' => __( 'Country' ),
        );

        register_taxonomy( 'movie_country', 'movie', array(
          'hierarchical' => false,
          'labels' => $labelsf,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'country', 'with_front' => false ),
        ) );


          //awards
        $labelsg = array(
          'name' => _x( 'Awards', 'taxonomy general name' ),
          'singular_name' => _x( 'Award', 'taxonomy singular name' ),
          'search_items' =>  __( 'Search Awards' ),
          'popular_items' => __( 'Popular Awards' ),
          'all_items' => __( 'All Awards' ),
          'parent_item' => null,
          'parent_item_colon' => null,
          'edit_item' => __( 'Edit Award' ),
          'update_item' => __( 'Update Award' ),
          'add_new_item' => __( 'Add New Award' ),
          'new_item_name' => __( 'New Award Name' ),
          'separate_items_with_commas' => __( 'Separate awards with commas' ),
          'add_or_remove_items' => __( 'Add or remove award' ),
          'choose_from_most_used' => __( 'Choose from the most used awards' ),
          'menu_name' => __( 'Awards' ),
        );

        register_taxonomy( 'movie_awards', 'movie', array(
          'hierarchical' => false,
          'labels' => $labelsg,
          'show_ui' => true,
          'update_count_callback' => '_update_post_term_count',
          'query_var' => true,
          'rewrite' => array( 'slug' => 'awards', 'with_front' => false ),
        ) );

        // incase the movie data should be associated with something else entirely
        $post_types = array();
        foreach( get_post_types() as $post_type ) {
            if ( post_type_supports( $post_type, 'movie' ) )
                $post_types[] = $post_type;
        }

        // associate things with things
        foreach( $post_types as $post_type ) {
            register_taxonomy_for_object_type( 'movie_genre', $post_type );
            register_taxonomy_for_object_type( 'movie_actor', $post_type );
            register_taxonomy_for_object_type( 'movie_director', $post_type );
            register_taxonomy_for_object_type( 'movie_writer', $post_type );
            register_taxonomy_for_object_type( 'movie_certificate', $post_type );
            register_taxonomy_for_object_type( 'movie_awards', $post_type );
            register_taxonomy_for_object_type( 'post_tag', 'movie' );
        }

    }


// Custom meta for awards taxo
function taxo_add_new_meta_field() {
  // this will add the custom meta field to the add new term page
  ?>
  <div class="form-field">
    <label for="award_option">Award Option</label>
    <input type="text" name="award_option" id="award_option" value="">
    <p class="description">Special options to use in code</p>
  </div>
<?php
}
add_action( 'movie_awards_add_form_fields', 'taxo_add_new_meta_field', 10, 2 );

function save_taxonomy_custom_meta( $term_id ) {
  if ( isset( $_POST['award_option'] ) ) {
    update_term_meta($term_id, 'award_option', $_POST['award_option']);
  }
}  
add_action( 'edited_movie_awards', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_movie_awards', 'save_taxonomy_custom_meta', 10, 2 );

function taxonomy_edit_meta_field( $term ) {
  $term_value = get_term_meta($term->term_id, 'award_option', true);
?>
  <tr class="form-field">
  <th scope="row" valign="top"><label for="award_option">Award Option</label></th>
    <td>
      <input type="text" name="award_option" id="award_option" value="<?php echo esc_attr( $term_value ) ? esc_attr( $term_value ) : ''; ?>">
      <p class="description">Enter a value for this field</p>
    </td>
  </tr>
<?php
}
add_action( 'movie_awards_edit_form_fields', 'taxonomy_edit_meta_field', 10, 2 );






//add_action('wp', 'import_from_tmdb');
function import_from_tmdb(){
  //error_log("Start Cron Import");
  global $import_type, $iupdate, $istartdate, $ienddate, $iyear;
  wp_clear_scheduled_hook( 'vma_import_movies');
  wp_schedule_single_event(time()-3600, 'vma_import_movies');
}

add_action( 'vma_import_movies', 'tmdb_imp', 10, 5 );


if(function_exists($_GET['f'])) {
  global $import_type, $iupdate, $istartdate, $ienddate, $iyear;
  $import_type = 1; 
  $iupdate = $_GET[ 'vma_update' ]; 
  $istartdate = $_GET[ 'vma_startdate' ];
  $iyear = $_GET[ 'vma_year' ]; 
  $ienddate = $_GET[ 'vma_enddate' ]; 
  $_GET['f']();
  //header("Location: admin.php?page=movie_importer&msg=Import Complete");
  error_log("Start Cron Import");
}



add_action('wp', 'vma_movie_importerc');
function vma_movie_importerc() {
  global $import_type, $iupdate, $istartdate, $ienddate, $iyear;
  $import_type = 3; 
  $istartdate = date('Y-m-d', strtotime("now -30 days") );
  $iyear = date("Y");
  $ienddate = date('Y-m-d', strtotime("now +15 days") );
  $iupdate = 1; 
  //wp_clear_scheduled_hook( 'vma_movie_importerc');
  if ( ! wp_next_scheduled('vma_movie_importerc')) {
    wp_schedule_event( time(), 'weekly', 'vma_movie_importerc' );
  }
}

add_action('vma_movie_importerc', 'tmdb_imp', 10 ,5 );





function tmdb_imp(){

  global $import_type, $iupdate, $istartdate, $ienddate, $iyear;
  
  if ( $import_type == 3 ) {

  error_log("Start Cron Import '$import_type', '$iupdate', $istartdate, $ienddate, $iyear as parameters.");

  }

  if ( $import_type == 1 ) {

  error_log("Start Manual Import '$import_type', '$iupdate', $istartdate, $ienddate, $iyear as parameters.");

  }

  if ( $import_type == 1 && $istartdate !='' && $ienddate !='' && $iyear !='' || $import_type == 3 && $istartdate !='' && $ienddate !='' && $iyear !='' ) {
  
        $startdate = $istartdate ;
        $enddate = $ienddate ;
        $vma_year = $iyear ;

        $tmdb_calla = "https://api.themoviedb.org/3/discover/movie?api_key=".$API_KEY."&primary_release_year=".$vma_year."&primary_release_date.gte=".$startdate."&primary_release_date.lte=".$enddate."&page=1&sort_by=release_date.asc&vote_count.gte=".$_GET[ 'popularity' ]; //certification_country=US&certification.lte=NC-17
        $movieinfo = tmdb_curl($tmdb_calla);
        $nb_pages = $movieinfo->total_pages;

        $i=0;

      while ($i <= $nb_pages) {
        
        //set_time_limit( 20 );
        $i++;
        $tmdb_call = "https://api.themoviedb.org/3/discover/movie?api_key=".$API_KEY."&primary_release_year=".$vma_year."&primary_release_date.gte=".$startdate."&primary_release_date.lte=".$enddate."&page=".$i."&sort_by=release_date.asc&vote_count.gte=".$_GET[ 'popularity' ]; //&certification_country=US&certification.lte=NC-17
        $movies = tmdb_curl($tmdb_call);

        foreach ($movies->results as $movie) {

            set_time_limit( 15 );

            $tmdb_movie_call = "https://api.themoviedb.org/3/movie/".$movie->id."?api_key=".$API_KEY."&append_to_response=credits,trailers,releases";
            $movie_data = tmdb_curl($tmdb_movie_call);

                $new_post = array(
                'post_title' => $movie_data->title,
                'post_content' => $movie_data->overview,
                'post_excerpt' => $movie_data->tagline,
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => 1,
                'post_type' => 'movie',
                'post_category' => array(0)
                );
                if (!get_page_by_title($movie_data->title, 'OBJECT', 'movie')) {
                $post_id = wp_insert_post($new_post);
                } else {
                  if($iupdate == 1) {
                    $post = get_page_by_title($movie_data->title, 'OBJECT', 'movie');
                    $post_id = $post->ID;
                    $new_post['ID'] = $post->ID;
                    wp_update_post( $new_post );
                  } else {
                    continue;
                  }
                }
                update_post_meta($post_id,'tmdb_original_title',$movie_data->original_title);
                update_post_meta($post_id,'tmdb_movie_id',$movie_data->id);
                update_post_meta($post_id,'tmdb_backdrop',$movie_data->backdrop_path);
                // uploadPix($movie_data->backdrop_path,$iupdate,2);
                update_post_meta($post_id,'tmdb_poster',$movie_data->poster_path);
                // uploadPix($movie_data->poster_path,$iupdate,1);
                update_post_meta($post_id,'tmdb_imdb_id',$movie_data->imdb_id);
                update_post_meta($post_id,'tmdb_url',$movie_data->homepage);
                //$lastt = count($movie_data->trailers->youtube);
                update_post_meta($post_id,'tmdb_trailer',$movie_data->trailers->youtube[0]->source);
                update_post_meta($post_id,'tmdb_release_date',$movie_data->release_date);
                update_post_meta($post_id,'tmdb_year',date('Y', strtotime($movie_data->release_date)));
                // prep taxonomy terms
                $genres = array();
                foreach( $movie_data->genres as $genre )
                    $genres[] = $genre->name;

          $directors = array();
          $writers = array();
          $actors = array();
          $certificates = array();
          $countries = array();

          foreach( $movie_data->credits->cast as $actor ) {
              $actors[] = $actor->name;
          }

          foreach( $movie_data->releases->countries as $certif ) {
              $certificates[] = $certif->certification;
          }

          foreach( $movie_data->production_countries as $country ) {
              $countries[] = $country->iso_3166_1;
          }

          foreach( $movie_data->credits->crew as $crew ) {
              $taxonomy = '';
              if ( stristr( $crew->department, 'directing' ) ) {
                  $directors[] = $crew->name;
                  $taxonomy = 'movie_director';
              }
              if ( stristr( $crew->department, 'writing' ) ) {
                  $writers[] = $crew->name;
                  $taxonomy = 'movie_writer';
              }
          }

          // set terms
          wp_set_object_terms( $post_id, $actors, 'movie_actor' );
          wp_set_object_terms( $post_id, $genres, 'movie_genre' );
          wp_set_object_terms( $post_id, $directors, 'movie_director' );
          wp_set_object_terms( $post_id, $writers, 'movie_writer' );
          wp_set_object_terms( $post_id, $certificates, 'movie_certificate' );
          wp_set_object_terms( $post_id, $countries, 'movie_country' );


                //update cast and crew pics
                foreach( $movie_data->credits->cast as $actor ) {
                  $castid = get_term_by( 'name', $actor->name, 'movie_actor' );
                  update_term_meta($castid->term_id, 'photo', $actor->profile_path);
                  // uploadPix($actor->profile_path,$iupdate,0);
                }

                foreach( $movie_data->credits->crew as $crew ) {
                    $taxonomy = '';
                    if ( stristr( $crew->department, 'directing' ) ) {
                      $crewid = get_term_by( 'name', $crew->name, 'movie_director' );
                      update_term_meta($crewid->term_id, 'photo', $crew->profile_path);
                      // uploadPix($crew->profile_path,$iupdate,0);
                    }
                }

        }
      }
  generate_search_file();
  generate_carousel();
//header("Location: admin.php?page=movie_importer&msg=Import Complete");
  }
}



function tmdb_imp_uniq(){

  if ( $_GET[ 'tmdb_import' ] == 2 && $_GET[ 'movie_id' ] && current_user_can( 'manage_options' ) ) {

    $m = $_GET[ 'movie_id' ];

    error_log("Start Single Movie Import '$m' as parameters.");

      $tmdb_movie_call = "https://api.themoviedb.org/3/movie/".$m."?api_key=".$API_KEY."&append_to_response=credits,trailers,releases";
      $movie_data = tmdb_curl($tmdb_movie_call);

      //if (!get_page_by_title($movie->title, 'OBJECT', 'movie')) :
          $new_post = array(
          'post_title' => $movie_data->title,
          'post_content' => $movie_data->overview,
          'post_excerpt' => $movie_data->tagline,
          'post_status' => 'publish',
          'post_date' => date('Y-m-d H:i:s'),
          'post_author' => 1,
          'post_type' => 'movie',
          'post_category' => array(0)
          );
          if (!get_page_by_title($movie_data->title, 'OBJECT', 'movie')) {
          $post_id = wp_insert_post($new_post);
          } else {
            $post = get_page_by_title($movie_data->title, 'OBJECT', 'movie');
            $post_id = $post->ID;
            $new_post['ID'] = $post->ID;
            wp_update_post( $new_post );
          }
          update_post_meta($post_id,'tmdb_movie_id',$movie_data->id);
          update_post_meta($post_id,'tmdb_backdrop',$movie_data->backdrop_path);
          // uploadPix($movie_data->backdrop_path,1,2);
          update_post_meta($post_id,'tmdb_poster',$movie_data->poster_path);
          // uploadPix($movie_data->poster_path,1,1);
          update_post_meta($post_id,'tmdb_imdb_id',$movie_data->imdb_id);
          update_post_meta($post_id,'tmdb_url',$movie_data->homepage);
          //$lastt = count($movie_data->trailers->youtube);
          update_post_meta($post_id,'tmdb_trailer',$movie_data->trailers->youtube[0]->source);
          update_post_meta($post_id,'tmdb_release_date',$movie_data->release_date);
          update_post_meta($post_id,'tmdb_year',date('Y', strtotime($movie_data->release_date)));
          // prep taxonomy terms
          $genres = array();
          foreach( $movie_data->genres as $genre )
              $genres[] = $genre->name;

          $directors = array();
          $writers = array();
          $actors = array();
          $certificates = array();
          $countries = array();

          foreach( $movie_data->credits->cast as $actor ) {
              $actors[] = $actor->name;
          }

          foreach( $movie_data->releases->countries as $certif ) {
              $certificates[] = $certif->certification;
          }

          foreach( $movie_data->production_countries as $country ) {
              $countries[] = $country->iso_3166_1;
          }

          foreach( $movie_data->credits->crew as $crew ) {
              $taxonomy = '';
              if ( stristr( $crew->department, 'directing' ) ) {
                  $directors[] = $crew->name;
                  $taxonomy = 'movie_director';
              }
              if ( stristr( $crew->department, 'writing' ) ) {
                  $writers[] = $crew->name;
                  $taxonomy = 'movie_writer';
              }
          }

          // set terms
          wp_set_object_terms( $post_id, $actors, 'movie_actor' );
          wp_set_object_terms( $post_id, $genres, 'movie_genre' );
          wp_set_object_terms( $post_id, $directors, 'movie_director' );
          wp_set_object_terms( $post_id, $writers, 'movie_writer' );
          wp_set_object_terms( $post_id, $certificates, 'movie_certificate' );
          wp_set_object_terms( $post_id, $countries, 'movie_country' );


          //update cast and crew pics
          foreach( $movie_data->credits->cast as $actor ) {
            $castid = get_term_by( 'name', $actor->name, 'movie_actor' );
            update_term_meta($castid->term_id, 'photo', $actor->profile_path);
            // uploadPix($actor->profile_path,1,0);
          }

          foreach( $movie_data->credits->crew as $crew ) {
              $taxonomy = '';
              if ( stristr( $crew->department, 'directing' ) ) {
                $crewid = get_term_by( 'name', $crew->name, 'movie_director' );
                update_term_meta($crewid->term_id, 'photo', $crew->profile_path);
                // uploadPix($crew->profile_path,1,0);
              }
          }


//header("Location: admin.php?page=movie_importer&msg=Import Complete");

  }

}

function grabimg_url($img,$from=0) {
if($from==0) {
 $path = "http://image.tmdb.org/t/p/w500".$img;
} 
if($from==1) {
 $path = "http://image.tmdb.org/t/p/w342".$img;
} 
if($from==2) {
 $path = "http://image.tmdb.org/t/p/w1280".$img;
} 

 return $path;
}

function uploadPix($img,$replace=0,$what=0){

$doc_root = ABSPATH;
//what = 0 people 1 poster 2 backdrop
if($what == 0) { $dira = $doc_root.'wp-content/uploads/people/sm/'; $dirb = $doc_root.'wp-content/uploads/people/med/'; $smsize = '150,225'; $medsize = '400,600'; }
if($what == 1) { $dira = $doc_root.'wp-content/uploads/poster/sm/'; $dirb = $doc_root.'wp-content/uploads/poster/med/';  $smsize = '100,150'; $medsize = '250,375'; }
if($what == 2) { $dira = $doc_root.'wp-content/uploads/backdrop/sm/'; $dirb = $doc_root.'wp-content/uploads/backdrop/med/';  $smsize = '500,280'; $medsize = '950,535'; }
$dir = $doc_root.'wp-content/uploads/';


  if (file_exists($dira.$img) && $replace==1) {
    $up = wp_upload_bits($img,null,@file_get_contents(grabimg_url($img,$what)));
   
        $image = wp_get_image_editor( $dir.$img );

        if ( ! is_wp_error( $image ) ) {
            $image->resize( $smsize, true );
            $image->save( $dira.$img );
        }

        $imageb = wp_get_image_editor( $dir.$img );

        if ( ! is_wp_error( $imageb ) ) {
            $imageb->resize( $medsize, true );
            $imageb->save( $dirb.$img );
        }

      unlink($dir.$img);

  } 

  if (file_exists($dira.$img) && $replace==0) {
    unlink($dir.$img);
    return;
  }

  if (!file_exists($dira.$img) && $replace==0) {
    $up = wp_upload_bits($img,null,@file_get_contents(grabimg_url($img,$what)));
   
        $image = wp_get_image_editor( $dir.$img );

        if ( ! is_wp_error( $image ) ) {
            $image->resize( $smsize, true );
            $image->save( $dira.$img );
        }

        $imageb = wp_get_image_editor( $dir.$img );

        if ( ! is_wp_error( $imageb) ) {
            $imageb->resize( $medsize, true );
            $imageb->save( $dirb.$img );
        }

      unlink($dir.$img);

  }

  if (!file_exists($dira.$img) && $replace==1) {
    $up = wp_upload_bits($img,null,@file_get_contents(grabimg_url($img,$what)));
   
        $image = wp_get_image_editor( $dir.$img );

        if ( ! is_wp_error( $image ) ) {
            $image->resize( $smsize, true );
            $image->save( $dira.$img );
        }

        $imageb = wp_get_image_editor( $dir.$img );

        if ( ! is_wp_error( $imageb ) ) {

            $imageb->resize( $medsize, true );
            $imageb->save( $dirb.$img );
            
        }

      unlink($dir.$img);

  }


}





function tmdb_curl($tmdb_call){
    $cu = curl_init();
    curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($cu, CURLOPT_TIMEOUT, 8);
    curl_setopt($cu, CURLOPT_URL, $tmdb_call);
    $data = curl_exec($cu);
    if($data === false) {  error_log('Curl error: ' . curl_error($cu). $tmdb_call, 0 ); }
    curl_close($cu);
    return json_decode( $data );
}


?>