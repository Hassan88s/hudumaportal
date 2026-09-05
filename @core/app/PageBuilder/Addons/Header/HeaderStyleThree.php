<?php


namespace App\PageBuilder\Addons\Header;

use App\Country;
use App\Page;
use App\PageBuilder\Fields\ColorPicker;
use App\PageBuilder\Fields\IconPicker;
use App\PageBuilder\Fields\Image;
use App\PageBuilder\Fields\Slider;
use App\PageBuilder\Fields\Switcher;
use App\PageBuilder\Fields\Text;
use App\PageBuilder\Traits\LanguageFallbackForPageBuilder;
use App\Category;
use App\ServiceArea;
use App\ServiceCity;
use App\User;

class HeaderStyleThree extends \App\PageBuilder\PageBuilderBase
{
    use LanguageFallbackForPageBuilder;

    public function preview_image()
    {
        return 'home_three/header_3.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();


        $output .= Text::get([
            'name' => 'title',
            'label' => __('Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);
        $output .= Text::get([
            'name' => 'subtitle',
            'label' => __('Subtitle'),
            'value' => $widget_saved_values['subtitle'] ?? null,
        ]);
        $output .= Text::get([
            'name' => 'service_type',
            'label' => __('Service Type'),
            'value' => $widget_saved_values['service_type'] ?? null,
        ]);
        $output .= IconPicker::get([
            'name' => 'service_icon',
            'label' => __('Service Icon'),
            'value' => $widget_saved_values['service_icon'] ?? null,
        ]);
        $output .= Text::get([
            'name' => 'service_link',
            'label' => __('Service Link'),
            'value' => $widget_saved_values['service_link'] ?? null,
        ]);

        $output .= ColorPicker::get([
            'name' => 'header_background_color',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['header_background_color'] ?? null,
        ]);

        $output .= Image::get([
            'name' => 'dot_image',
            'label' => __('Banner Dot Image'),
            'value' => $widget_saved_values['dot_image'] ?? null,
            'dimensions' => '163x163'
        ]);
        $output .= Image::get([
            'name' => 'banner_image',
            'label' => __('Banner Image'),
            'value' => $widget_saved_values['banner_image'] ?? null,
            'dimensions' => '46x46'
        ]);

        $output .= Image::get([
            'name' => 'image',
            'label' => __('Background Image'),
            'value' => $widget_saved_values['image'] ?? null,
            'dimensions' => '795x1139'
        ]);


        $output .= Switcher::get([
            'name' => 'country_show_hide',
            'label' => __('Country'),
            'value' => $widget_saved_values['country_show_hide'] ?? null,
            'info' => __('Country wise Service Search Hide/Show')
        ]);

        $output .= Switcher::get([
            'name' => 'city_show_hide',
            'label' => __('City'),
            'value' => $widget_saved_values['city_show_hide'] ?? null,
            'info' => __('City wise Service Search Hide/Show')
        ]);

        $output .= Switcher::get([
            'name' => 'area_show_hide',
            'label' => __('Area'),
            'value' => $widget_saved_values['area_show_hide'] ?? null,
            'info' => __('Area wise Service Search Hide/Show')
        ]);

        // CLIENT BANNER
        $output .= Switcher::get([
            'name' => 'client_banner_area',
            'label' => __('Client Banner'),
            'value' => $widget_saved_values['client_banner_area'] ?? null,
            'info' => __('Client Banner Hide/Show')
        ]);

        // REVIEW BANNER
        $output .= Switcher::get([
            'name' => 'review_banner_area',
            'label' => __('Review Banner'),
            'value' => $widget_saved_values['review_banner_area'] ?? null,
            'info' => __('Review Banner Hide/Show')
        ]);

        // Banner Dot Image
        $output .= Switcher::get([
            'name' => 'banner_dot_image_show_hide',
            'label' => __('Banner Dot Image'),
            'value' => $widget_saved_values['banner_dot_image_show_hide'] ?? null,
            'info' => __('Banner Dot Image Hide/Show')
        ]);

        // HOME SEARCH AREA
        $output .= Switcher::get([
            'name' => 'home_search_area',
            'label' => __('Home Search'),
            'value' => $widget_saved_values['home_search_area'] ?? null,
            'info' => __('Home Search Hide/Show')
        ]);

        // POPULAR CATEGORY WISE SEARCH
        $output .= Switcher::get([
            'name' => 'popular_category_wise_search',
            'label' => __('Category wise Search'),
            'value' => $widget_saved_values['popular_category_wise_search'] ?? null,
            'info' => __('Category wise search Hide/Show')
        ]);


        $output .= Slider::get([
            'name' => 'padding_top',
            'label' => __('Padding Top'),
            'value' => $widget_saved_values['padding_top'] ?? 260,
            'max' => 500,
        ]);
        $output .= Slider::get([
            'name' => 'padding_bottom',
            'label' => __('Padding Bottom'),
            'value' => $widget_saved_values['padding_bottom'] ?? 190,
            'max' => 500,
        ]);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render() : string
    {
        $settings = $this->get_settings();

        $title = $settings['title'];
        $subtitle = $settings['subtitle'];

        $country_show_hide =$settings['country_show_hide'] ?? '';
        $city_show_hide =$settings['city_show_hide'] ?? '';
        $area_show_hide =$settings['area_show_hide'] ?? '';

        $client_banner_show_hide =$settings['client_banner_area'] ?? '';
        $review_banner_show_hide =$settings['review_banner_area'] ?? '';
        $banner_dot_image_show_hide =$settings['banner_dot_image_show_hide'] ?? '';
        $home_search_area_show_hide =$settings['home_search_area'] ?? '';
        $popular_category_wise_search =$settings['popular_category_wise_search'] ?? '';
        $header_background_color =$settings['header_background_color'] ?? '';

        $explode = explode(" ",$title);
        $title_end = end($explode);
        $last_space_position = strrpos($title, ' ');
        $title_start = substr($title, 0, $last_space_position);

        $service_type = $settings['service_type'];
        $service_icon = $settings['service_icon'];
        $service_link = $settings['service_link'];
        $image = render_image_markup_by_attachment_id($settings['image']);
        $banner_dot_image = render_image_markup_by_attachment_id($settings['dot_image']);
        $banner_image = render_image_markup_by_attachment_id($settings['banner_image']);
        $happy_clients = __('Happy Clients');
        $happy_clients_count = User::where('user_type','1')->where('user_status','1')->count();
        $search_placeholder = __('What are you looking for?');
        $select_country = __('Select Country');
        $select_city = __('Select City');
        $select_area = __('Select Area');
        $route = route('service.list.category');
        $search_route = route('frontend.home.search.single');
        $popular = __('Popular:');

        $service_countries = Country::where('status',1)->get();
        $categories = Category::whereHas('services')->select('id','name','slug')->take(5)->inRandomOrder()->get();
        $country_markup = '';
        $service_markup = '';
        $category_markup = '';


        foreach ($service_countries as $country){
            $country_id = $country->id;
            $country_name = $country->country;
            $country_markup.= <<<COUNTRYMARKUP
            <option value="{$country_id}">{$country_name}</option>
            COUNTRYMARKUP;
        }

        foreach ($categories as $cat){
            $category_name = $cat->name;
            $category_slug = $cat->slug;
            $service_markup.= <<<SERVICECATEGORY
            <option value="{$category_name}">{$category_name}</option>
SERVICECATEGORY;
        }
foreach ($categories as $cat){
    $category_name = $cat->name;
    $category_slug = $cat->slug;
    $category_markup.= <<<CATEGORY
    <li><a href="{$route}/{$category_slug}"> {$category_name} </a></li>
CATEGORY;
        
}
$country_show_hide_markup = '';
$city_show_hide_markup = '';
$area_show_hide_markup = '';

if ($country_show_hide == 'on'){
    $country_show_hide_markup.= <<<COUNTRYSHOWHIDE
         <div class="banner-address-select">
            <select name="service_country_id" id="service_country_id" class="country-wrapper">
                <option value="">{$select_country}</option>
                {$country_markup}
            </select>
        </div>
COUNTRYSHOWHIDE;
}
if ($city_show_hide == 'on'){
    $fetch_cities = '';
    if($country_show_hide !== 'on'){
        $all_cities = ServiceCity::where("status" ,1)->get();

        foreach ($all_cities as $cities){
            $fetch_cities .= '<option value='.$cities->id.'>'.$cities->service_city.'</option>';
        }
    }
   

    $city_show_hide_markup.= <<<CITYSHOWHIDE
          <div class="banner-address-select">
            <select name="service_city_id" id="service_city_id">
                <option value="">{$select_city}</option> 
                {$fetch_cities }                                           
            </select>
        </div>
CITYSHOWHIDE;
}

// todo: first check if country and city is disable get area list
$country_city_service_area_markup =  '';
if (empty($country_show_hide && $city_show_hide)){
    $services_area = ServiceArea::select("id", "service_area", "status")->where("status", 1)->get();
foreach ($services_area as $service_area) {
    $service_area_name = $service_area->service_area;
    $service_area_id = $service_area->id;
    $country_city_service_area_markup .= <<<SERVICEAREA
    <option value="{$service_area_id}">{$service_area_name}</option>
SERVICEAREA;
 }
}

if ($area_show_hide == 'on'){
    $area_show_hide_markup.= <<<AREASHOWHIDE
         <div class="banner-address-select">
            <select name="service_area_id" id="service_area_id">                
                <option value="">{$select_area}</option>    
                {$country_city_service_area_markup}                                        
            </select>
        </div>
AREASHOWHIDE;
}

// google map start
  $google_map_markup = '';
  if (!empty(get_static_option('google_map_settings'))) {
      $sel_location_text = get_static_option('google_map_search_button_title') ?? __('Set Location');
      $placeholder_text_map = get_static_option('google_map_search_placeholder_title') ?? __('Search location here');
      $url_search_services_list = get_static_option('select_home_page_search_service_page_url') ?? '/service-list';
      $google_map_service_route = url($url_search_services_list);
      $myLocationIcon = asset('assets/frontend/img/static/my-location.png');

      $google_map_markup.= <<<AUTOCOMPLETE
          <form action="{$google_map_service_route}" class="banner-search-form banner-search-location">                              
            <div class="single-input">               
                <input class="form--control" name="autocomplete" id="autocomplete" type="text" placeholder="{$placeholder_text_map}">
                <input class="form--control" name="change_address_new" id="change_address_new" type="hidden" value="">
                <div class="location-search-icon" id="myLocationGetAddress"> <img src="{$myLocationIcon}" alt="my-location-icon"> </div>                
                <button type="submit" class="setLocation_btn"> {$sel_location_text} </button>
            </div>     
       </form>
AUTOCOMPLETE;
}
// google map end



// client banner area
        $client_area_banner_markup = '';
        if(!empty($client_banner_show_hide)) {
            $client_area_banner_markup.= <<<CLIENTBANNERAREASHOWHIDE
  <div class="banner-client">
    <div class="smile-contents-all">
        <div class="thumb-smile">
        {$banner_image}
        </div>
        <div class="smile-content">
            <span class="smile-title">{$happy_clients_count}</span>
            <span class="smile-para">{$happy_clients} </span>
        </div>
    </div>
    </div>
CLIENTBANNERAREASHOWHIDE;
        }


// review banner area
$review_area_banner_markup = '';
if(!empty($review_banner_show_hide)) {
    $review_area_banner_markup.= <<<REVIEWBANNERAREAHOWHIDE
     <div class="banner-cleaning-service">
        <div class="icon">
            <i class="{$service_icon}"></i>
        </div>
        <div class="icon-contents">
            <span class="thumb-cleaning-title"> <a href="{$service_link}"> {$service_type} </a> </span>
            <ul class="review-cleaning">
                <li> <i class="las la-star"></i> </li>
                <li> <i class="las la-star"></i> </li>
                <li> <i class="las la-star"></i> </li>
                <li> <i class="las la-star"></i> </li>
                <li> <i class="las la-star"></i> </li>
            </ul>
        </div>
    </div>
REVIEWBANNERAREAHOWHIDE;
}

// banner dot image
$banner_dot_image_hide_show_markup = '';
if(!empty($banner_dot_image_show_hide)) {
$banner_dot_image_hide_show_markup.= <<<REVIEWBANNERAREAHOWHIDE
<div class="banner-dot-shape">
    {$banner_dot_image}
</div>
REVIEWBANNERAREAHOWHIDE;
}

// home search area
$home_search_area_markup = '';
if(!empty($home_search_area_show_hide)) {
 $home_search_area_markup.= <<<HOMESEARCHAREASHOWHIDE
<div class="single-input">
<input class="form--control" name="home_search" id="home_search" type="text" placeholder="{$search_placeholder}" autocomplete="off">
<div class="icon-search">
    <i class="las la-search"></i>
</div>
<button type="submit"> <i class="las la-search"></i> </button>
</div>
HOMESEARCHAREASHOWHIDE;
}



// Popular category wise search
$popular_category_wise_search_markup = '';
if(!empty($popular_category_wise_search)) {
    $popular_category_wise_search_markup.= <<<POPULARCATEGORYWISESEARCH
<div class="banner-keywords">
<span class="keyword-title"> {$popular} </span>
<ul class="keyword-tag">
   {$category_markup}
</ul>
</div>
POPULARCATEGORYWISESEARCH;
}

    $section_bg = '';
    if(empty($header_background_color)){
        $section_bg = 'gradient-bg-1';
    }

return <<<HTML
<!-- Banner area Starts -->
<div class="banner-area home-three-banner {$section_bg}" style="background-color: {$header_background_color}">
        <div class="container container-two">
            <div class="row align-items-center">
                <div class="col-xl-5">
                    <div class="banner-right-contents style-02">
                        <div class="banner-right-thumb wow slideInLeft" data-wow-delay=".3s">
                            {$image}       
                            {$banner_dot_image_hide_show_markup}                            
                        </div>
                        {$review_area_banner_markup}
                        {$client_area_banner_markup}
                    </div>
                </div>
                
                <div class="col-xl-7">
                    <div class="banner-contents style-03">
                        <h1 class="banner-title">{$title_start} <span class="color-three"> {$title_end} </span> </h1>
                        <span class="title-top">{$subtitle}</span>
                        <div class="banner-bottom-content">                        
                            <!--google map autocomplete address -->
                             {$google_map_markup}                        
                            <form action="{$search_route}" class="banner-search-form">
                               <!--country, city area search -->    
                                <div class="header_03_form_select_wrap mt-4">
                                    {$country_show_hide_markup}
                                    {$city_show_hide_markup}     
                                    {$area_show_hide_markup}   
                                </div>                         
                                <!--Home search  area -->
                                {$home_search_area_markup}                                
                            </form>     
                            <span id="all_search_result"></span>                            
                            <!--POPULAR CATEGORY WISE SEARCH -->
                            {$popular_category_wise_search_markup}                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Banner area end -->
    
HTML;
}

    public function addon_title()
    {
        return __('Header: 03');
    }
}