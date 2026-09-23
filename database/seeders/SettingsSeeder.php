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

        $sections = [
            ['hero', 'Hero', 'Where little ones learn by playing', 'An English-language nursery in Hadayek Al Ahram and Sheikh Zayed for children from 6 months to school age. Fourteen years of happy mornings, messy hands and big first steps.', null, 'Book a visit', '/enroll'],
            ['class_finder', 'Class finder', 'Which class will your child join?', 'Enter your child’s birthday and we’ll show you their class and what their days will look like.', null, null, null],
            ['why', 'Why Marshmallow', 'What parents notice first', 'The things families tell us made them choose Marshmallow.', null, null, null],
            ['classes', 'Our classes', 'Six classes, one for every stage', 'Children are grouped by age so every activity fits where they are right now.', null, 'See all classes', '/classes'],
            ['activities', 'Activities', 'A week full of discovery', 'Academics in English, three languages, gymnastics twice a week, science, art, cooking and a trip every month.', null, 'Explore activities', '/activities'],
            ['safety', 'Safety & care', 'Safe, clean and well fed', 'Cameras in every class, bathroom and garden, four security doors, a fire alarm system and four fresh meals a day.', null, 'How we keep children safe', '/safety'],
            ['camps', 'Camps', 'Camps for every school holiday', 'Summer, winter, spring and autumn camps for children aged 4 to 12 — from pottery and tie-dye to robotics and programming.', null, 'View camps', '/camps'],
            ['testimonials', 'Parents', 'In parents’ words', null, null, null, null],
            ['partners', 'Partners', 'Ready for the next school', 'We work with international schools across West Cairo to help our graduates move on with confidence.', null, null, null],
            ['gallery', 'Gallery', 'A peek inside our days', null, null, 'Open the gallery', '/gallery'],
            ['faq', 'FAQ', 'Questions parents ask us', null, null, null, null],
            ['branches', 'Branches', 'Visit a branch near you', 'Book a visit and see the classrooms, garden and team for yourself.', null, null, null],
            ['enroll_cta', 'Enrollment call to action', 'Spaces for 2026–2027 are filling up', 'Tell us a little about your child and our admissions team will call you back within one working day.', null, 'Book a visit', '/enroll'],
        ];

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
            'branches' => ['Branches & contact', 'Branches & contact | Marshmallow Nursery Hadayek Al Ahram & Sheikh Zayed', 'Addresses, phone numbers and maps for our Hadayek Al Ahram and Sheikh Zayed branches.'],
            'careers' => ['Careers', 'Careers & internships | Marshmallow Nursery', 'Join our team of teachers, or apply for our internship program for ages 15+.'],
            'enroll' => ['Enroll / Book a visit', 'Book a visit | Marshmallow Nursery', 'Book a visit or ask about enrollment for 2026–2027.'],
        ];

        foreach ($pages as $key => [$label, $title, $description]) {
            SeoPage::updateOrCreate(['page_key' => $key], compact('label', 'title', 'description'));
        }
    }
}
