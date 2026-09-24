<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\SeoPage;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::put([
            'site_name' => 'Marshmallow Child Development Center',
            'short_name' => 'Marshmallow',
            'tagline' => 'A Unique Way of Learning',
            'main_phone' => '01012666625',
            'email' => 'admin@marshmallownursery.com',
            'whatsapp_number' => '01012666625',
            'facebook_url' => 'https://www.facebook.com/MarshmallowNursery',
            'messenger_url' => 'https://m.me/MarshmallowNursery',
            'instagram_url' => '',
            'tiktok_url' => '',
            'youtube_url' => '',
            'years_experience' => '14',
            'followers' => '50K+',
            'recommend_percent' => '96',
            'reviews_count' => '329',
            'working_days' => 'Sunday – Thursday',
            'working_hours' => '8:00 am – 4:00 pm',
            'weekend' => 'Friday & Saturday',
            'after_school' => 'After-school care 4:00 – 6:00 pm on request',
            'tour_hours' => 'Camera tours at reception, 10:00 am – 1:00 pm, by appointment',
            'academic_year_note' => 'The school year runs from September to June',
            'teaching_language' => 'English',
            'admissions_open' => '1',
            'admissions_label' => 'Admissions open for 2026–2027',
            'admission_years' => '2026-2027,2027-2028',
            'announcement' => 'Admissions are open for 2026–2027. Spaces in each class are limited.',
            'announcement_visible' => '1',
            'footer_about' => 'An English-language nursery in Giza where children from 6 months to school age learn through play. It features spacious, well-ventilated classrooms, four healthy meals a day and cameras in every room.',
            'hashtags' => '#Marshmallow_Child_Development_Center #LearningThroughPlay #EarlyChildhoodEducation',
            'ga4_id' => '',
            'meta_pixel_id' => '',
            'head_scripts' => '',
            'lead_notify_email' => 'admin@marshmallownursery.com',
            'lead_auto_assign' => '1',
            'thank_you_message' => 'Thank you! Our admissions team will call you within one working day to answer your questions and book your visit.',
        ]);

        // The homepage is one journey: happy children → who we are → what we do → the classes →
        // is it safe → what parents say → the schools we prepare for → the photos → come and visit.
        $sections = [
            ['hero', 'Hero (photo slider)', 'Where little ones learn by playing', 'An English-language nursery in Hadayek Al Ahram and Sheikh Zayed, for children from 6 months to school age.', null, 'Book a visit', '/enroll'],
            ['about', 'Who we are', 'Fourteen years of Marshmallow mornings', 'We opened in 2011 with one idea: children learn best when they are happy. Today two branches in Giza welcome children from 6 months to school age, in six classes grouped by age.', null, 'Meet the classes', '/classes'],
            ['offer', 'What we do', 'What your child does with us', 'Academics in English, three languages, gymnastics, science, art, music, cooking and a trip every month.', null, null, null],
            ['classes', 'Our classes', 'Six classes, one for every stage', 'Children are grouped by age, so every activity fits exactly where they are now.', null, 'See every class in detail', '/classes'],
            ['safety', 'Safety, cleanliness & meals', 'Safe, clean and well fed', 'Cameras in every class, bathroom and garden, four security doors, a fire alarm system, and four fresh meals a day.', null, 'More about safety', '/safety'],
            ['reviews', 'Parent reviews', 'In parents’ own words', 'Real recommendations written by Marshmallow families on Facebook.', null, null, null],
            ['partners', 'Partner schools', 'Ready for the next school', 'Our graduates move on to international schools across West Cairo, and we prepare them for it all year.', null, null, null],
            ['gallery', 'Gallery', 'A peek inside our days', 'Graduations, trips, science days and celebrations — this is what a year at Marshmallow looks like.', null, 'Open the gallery', '/gallery'],
            ['visit_cta', 'Come and visit', 'Come and see it for yourself', 'Book a visit, meet the team, and see the classrooms while the children are in them.', null, 'Book a visit', '/enroll'],
        ];

        // Sections that used to be on the homepage and now live inside other pages.
        Section::whereIn('key', ['class_finder', 'why', 'activities', 'camps', 'testimonials', 'faq', 'branches', 'enroll_cta'])
            ->update(['is_visible' => false]);

        foreach ($sections as $i => [$key, $name, $title, $subtitle, $body, $buttonText, $buttonUrl]) {
            Section::updateOrCreate(['key' => $key], [
                'name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'body' => $body,
                'button_text' => $buttonText, 'button_url' => $buttonUrl, 'sort_order' => $i, 'is_visible' => true,
            ]);
        }

        $pages = [
            'home' => ['Home', 'Marshmallow Child Development Center | Nursery in Hadayek Al Ahram & Sheikh Zayed', 'English-language nursery and child development center in Giza for children from 6 months to school age. Play-based learning, cameras in every class, 4 healthy meals and monthly trips.'],
            'classes' => ['Classes', 'Classes by age | Marshmallow Nursery', 'From Cupcake (6 months) to Cotton Candy (4 years to school age): find your child’s class and what they learn each day.'],
            'activities' => ['Activities', 'Activities & enrichment | Marshmallow Nursery', 'English, French and Arabic, gymnastics, science experiments, art, music, cooking, storytelling and a trip every month.'],
            'camps' => ['Camps', 'Summer, winter & holiday camps in Giza | Marshmallow', 'Holiday camps for ages 4–12: arts, pottery, science, robotics, programming and more, with meals included.'],
            'safety' => ['Safety & care', 'Safety, meals & transport | Marshmallow Nursery', 'Cameras in every class, bathroom and garden, security doors, fire alarms, four healthy meals a day and safe buses.'],
            'gallery' => ['Gallery', 'Photo gallery | Marshmallow Nursery', 'Graduations, trips, science days and celebrations at Marshmallow.'],
            'about' => ['About', 'About Marshmallow | 14 years of early childhood education', 'Fourteen years of play-based early childhood education in Giza.'],
            'visit' => ['Visit us', 'Visit Marshmallow Nursery | Hadayek Al Ahram & Sheikh Zayed', 'Our branches, opening hours, safety, meals and transport — and how to book your visit.'],
            'branches' => ['Branches & contact', 'Branches & contact | Marshmallow Nursery Hadayek Al Ahram & Sheikh Zayed', 'Addresses, phone numbers and maps for our Hadayek Al Ahram and Sheikh Zayed branches.'],
            'careers' => ['Careers', 'Careers & internships | Marshmallow Nursery', 'Join our team of teachers, or apply for our internship program for ages 15+.'],
            'enroll' => ['Enroll / Book a visit', 'Book a visit | Marshmallow Nursery', 'Book a visit or ask about enrollment for 2026–2027.'],
        ];

        foreach ($pages as $key => [$label, $title, $description]) {
            SeoPage::updateOrCreate(['page_key' => $key], compact('label', 'title', 'description'));
        }
    }
}
