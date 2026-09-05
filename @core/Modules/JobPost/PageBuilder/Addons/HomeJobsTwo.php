<?php


namespace Modules\JobPost\PageBuilder\Addons;

use App\PageBuilder\Fields\ColorPicker;
use App\JobPost;
use App\PageBuilder\Fields\Slider;
use App\PageBuilder\Fields\Number;
use App\PageBuilder\Fields\Text;
use App\PageBuilder\Traits\LanguageFallbackForPageBuilder;
use Illuminate\View\View;
use Modules\JobPost\Entities\BuyerJob;
use Modules\JobPost\Entities\JobRequest;
use Str;


class HomeJobsTwo extends \App\PageBuilder\PageBuilderBase
{
    use LanguageFallbackForPageBuilder;

    public function viewPath(){
        return "jobpost::pagebuilder.";
    }
    public function preview_image()
    {
        return 'home_three/home_jobs_two.jpg';
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
            'name' => 'explore_all',
            'label' => __('Explore Text'),
            'value' => $widget_saved_values['explore_all'] ?? null,
        ]);
        $output .= Text::get([
            'name' => 'explore_link',
            'label' => __('Explore Link'),
            'value' => $widget_saved_values['explore_link'] ?? null,
            'info' => __('enter the link where you want to redirect users after click'),
        ]);
        $output .= Number::get([
            'name' => 'items',
            'label' => __('Items'),
            'value' => $widget_saved_values['items'] ?? null,
            'info' => __('enter how many item you want to show in frontend'),
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
        $output .= ColorPicker::get([
            'name' => 'section_bg',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['section_bg'] ?? null,
            'info' => __('select color you want to show in frontend'),
        ]);
        $output .= Text::get([
            'name' => 'book_appointment',
            'label' => __('Apply Now Button Text'),
            'value' => $widget_saved_values['book_appointment'] ?? 'Apply Now',
        ]);

        $output .= Text::get([
            'name' => 'stating_at_title',
            'label' => __('Starting at Price Title'),
            'value' => $widget_saved_values['stating_at_title'] ?? null,
        ]);

        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }


    public function frontend_render() : string
    {
        $settings = $this->get_settings();
        $section_title =$settings['title'] ?? 'New Jobs';
        $stating_at_title_show = $settings['stating_at_title'] ??  __('Starting at');
        $explore_text =$settings['explore_all'] ?? 'Explore All';
        $explore_link =$settings['explore_link']?? '#';
        $items =$settings['items'] ?? 4;
        $book_now_text = $settings['book_now'] ??  'Apply Now';
        $padding_top = $settings['padding_top'];
        $padding_bottom = $settings['padding_bottom'];
        $section_bg = $settings['section_bg'];
        $current_date = date('Y-m-d h:i:s');

        // $all_jobs = BuyerJob::where('status', 1)
        //     ->where('is_job_on', 1)
        //     ->where('is_paid', 1)
        //     ->where('package_id', 1)
        //     ->where('dead_line', '>=' ,$current_date)
        //     ->OrderBy('id','DESC')
        //     ->take($items)->get();
        
        $all_jobs = BuyerJob::where('status', 1)
    ->where('is_job_on', 1)
    ->where('is_paid', 1)
    ->where('dead_line', '>=', $current_date)
    ->orderByRaw("FIELD(package_id, 3, 2, 1)")
    ->orderBy('id', 'DESC')
    ->take($items)
    ->get();


        return $this->renderBlade('home.home-jobs-two',[
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'section_title' => $section_title,
            'explore_link' => $explore_link,
            'explore_text' => $explore_text,
            'section_bg' => $section_bg,
            'all_jobs' => $all_jobs,
            'book_now_text' => $book_now_text,
            'stating_at_title_show' => $stating_at_title_show
        ]);

    }

    public function addon_title()
    {
        return __('Home Jobs Two');
    }
}