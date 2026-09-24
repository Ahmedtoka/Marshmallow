<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Camp;
use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\Highlight;
use App\Models\JobOpening;
use App\Models\Partner;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $highlights = [
            'why' => [
                ['14 years of experience', 'Families have trusted Marshmallow with their little ones since 2011.', 'medal', '#E8177F'],
                ['96% of parents recommend us', 'Based on 329 reviews from Marshmallow families on Facebook.', 'heart', '#2CBCC9'],
                ['Cameras in every room', 'Every class, bathroom and garden is covered, with four security doors and a fire alarm system.', 'shield', '#8479BD'],
                ['Four fresh meals a day', 'Breakfast, two snacks and a hot lunch, freshly made every day.', 'apple', '#7FA82A'],
                ['Qualified, caring teachers', 'Our team is certified in Positive Discipline for early childhood educators.', 'teacher', '#E8A317'],
                ['Learning through play', 'English academics, three languages, gymnastics, science and art in every week.', 'blocks', '#E8177F'],
            ],
            'safety' => [
                ['Cameras everywhere', 'Cameras cover every class, bathroom and the garden, and our team monitors classes throughout the day.', 'camera', '#8479BD'],
                ['Four security doors', 'Children never reach the street — every entrance has a controlled security door.', 'door', '#E8177F'],
                ['Fire alarm system', 'A full fire alarm system with regular drills so children know what to do.', 'bell', '#E8A317'],
                ['Abuse-prevention training', 'An annual awareness workshop with the SAFE team, specialists in child psychology and abuse prevention.', 'shield', '#2CBCC9'],
            ],
            'health' => [
                ['Deep cleaning and sterilization', 'Classrooms, toys and bathrooms are sterilized to a high standard every day.', 'sparkle', '#2CBCC9'],
                ['Regular handwashing', 'Handwashing is built into the routine before meals, after the garden and after the bathroom.', 'hands', '#7FA82A'],
                ['Limited class sizes', 'We cap the number of children in each class so every child gets attention.', 'users', '#E8177F'],
            ],
            'meals' => [
                ['Breakfast', 'A warm, balanced start to the day.', 'sun', '#E8A317'],
                ['Morning snack', 'Fresh fruit or a healthy bite.', 'apple', '#7FA82A'],
                ['Lunch', 'A freshly cooked hot meal.', 'bowl', '#E8177F'],
                ['Dessert & afternoon snack', 'A sweet treat before home time.', 'cupcake', '#8479BD'],
            ],
            'logistics' => [
                ['Safe school buses', 'Two routes: inside Hadayek Al Ahram, and along Al Haram to Maryoutia.', 'bus', '#E8A317'],
                ['Parent app & call center', 'Updates, photos and messages through our mobile app and call center.', 'phone', '#2CBCC9'],
                ['WhatsApp & Messenger', 'Reach the team quickly on WhatsApp or Facebook Messenger.', 'chat', '#7FA82A'],
                ['Camera tours', 'See the classes live on the reception screens, 10:00 am – 1:00 pm, by appointment.', 'camera', '#8479BD'],
            ],
            'credentials' => [
                ['Positive Discipline certified', 'Our management team holds the Positive Discipline for Early Childhood Educators certificate.', 'medal', '#E8177F'],
                ['SAFE abuse-awareness workshop', 'An annual workshop with specialists in emotional intelligence and child psychology.', 'shield', '#2CBCC9'],
                ['Honored by Majesty International Schools', 'Recognized by one of our partner schools for our graduates’ readiness.', 'star', '#E8A317'],
            ],
            'services' => [
                ['Opening hours', 'Sunday – Thursday, 8:00 am – 4:00 pm. Closed Friday and Saturday.', 'clock', '#2CBCC9'],
                ['After-school care', '4:00 – 6:00 pm on request, after the nursery day ends.', 'moon', '#8479BD'],
                ['School year', 'September to June, with camps in every holiday.', 'calendar', '#7FA82A'],
            ],
        ];

        foreach ($highlights as $group => $items) {
            foreach ($items as $i => [$title, $description, $icon, $color]) {
                Highlight::updateOrCreate(['group' => $group, 'title' => $title], compact('description', 'icon', 'color') + ['sort_order' => $i]);
            }
        }

        Camp::updateOrCreate(['slug' => 'winter-camp-2027'], [
            'title' => 'Winter Camp 2027', 'season' => 'winter', 'year' => 2027, 'age_from' => 4, 'age_to' => 12,
            'schedule' => 'Sunday – Thursday during the mid-year break', 'meals' => '3 meals included', 'badge' => 'Limited spaces',
            'summary' => 'Two weeks of making, building and experimenting while school is out.',
            'description' => 'Winter camp keeps children busy with a new project every day: pottery, science experiments, robotics and programming, cooking and games, with three meals included.',
            'activities' => ['Pottery making', 'Science experiments', 'Programming', 'Robotics', 'Cooking', 'Candle foam crafting', 'Glass painting', 'Fun competitions & games'],
            'is_active' => true, 'is_featured' => true, 'sort_order' => 1,
        ]);

        Camp::updateOrCreate(['slug' => 'summer-camp-2027'], [
            'title' => 'Summer Camp 2027', 'season' => 'summer', 'year' => 2027, 'age_from' => 4, 'age_to' => 12,
            'schedule' => 'Sunday – Thursday, June to August', 'meals' => '3 meals included', 'badge' => 'Limited spaces',
            'summary' => 'Our biggest camp of the year, with a different workshop every day.',
            'description' => 'From tie-dye and wood workshops to beach crafts and robotics, summer camp is a season of new skills and new friends.',
            'activities' => ['Gypsum creations', 'Arts & crafts', 'Glass painting', 'Puzzle stick projects', 'Pottery making', 'Science experiments', 'Fun competitions & games', 'Wood workshops', 'Candle foam crafting', 'Recycling projects', 'Tie-dye magic', 'Stencil art', 'Programming', 'Robotics', 'Cooking', 'Beach crafts', 'Mirror decorating'],
            'is_active' => true, 'is_featured' => false, 'sort_order' => 2,
        ]);

        $hadayek = Branch::where('slug', 'hadayek-al-ahram')->value('id');
        $albums = [
            ['Graduation 2026: Under the Sea', 'graduation', 'Our ocean-themed graduation ceremony.', '2026-06-20', null],
            ['Solar System Competition', 'activities', 'Children built and presented their own solar systems.', '2026-03-10', null],
            ['Pyjama Camping Party: Bedtime Stories', 'celebrations', 'A cosy evening of tents, torches and stories.', '2026-02-12', null],
            ['6th of October celebrations', 'celebrations', 'Flags, songs and costumes for October 6th.', '2025-10-06', $hadayek],
            ['Science Day', 'activities', 'Volcanoes, slime and color experiments.', '2026-04-15', null],
            ['Trip to Dolphina', 'trips', 'Our monthly trip to the dolphin show.', '2026-05-05', null],
            ['Summer Camp 2026', 'camps', 'Robotics, tie-dye and pottery at summer camp.', '2026-07-20', null],
            ['Our classrooms & garden', 'campus', 'A look at our classrooms, garden and play areas.', null, null],
            ['Animals visit', 'trips', 'The animals came to us — feeding, petting and lots of questions.', null, null],
            ['Circus day', 'trips', 'Acrobats, clowns and a lot of laughing.', null, null],
            ['Marshmallow Schools Expo', 'celebrations', 'The international schools we invite so parents can choose the next step.', null, null],
            ['Parents reviews', 'reviews', 'What Marshmallow families say about us, in their own words.', null, null],
        ];
        foreach ($albums as $i => [$title, $category, $description, $date, $branch]) {
            GalleryAlbum::updateOrCreate(['slug' => str($title)->slug()], [
                'title' => $title, 'category' => $category, 'description' => $description,
                'event_date' => $date, 'branch_id' => $branch, 'sort_order' => $i,
            ]);
        }

        // An album with no photos yet would show as an empty card, so keep it hidden until it has some.
        GalleryAlbum::doesntHave('photos')->update(['is_visible' => false]);
        GalleryAlbum::has('photos')->update(['is_visible' => true]);

        // Parent reviews now come from the Facebook page (see ReviewSeeder); the three we wrote by
        // hand are only seeded when there are none at all, so a fresh install is never empty.
        if (Testimonial::count() === 0) {
            $testimonials = [
                ['Hadeer Raafat', 'Marshmallow parent', 'We are so grateful for all the love, care, and support you gave our son throughout his time at your nursery. He truly enjoyed every day, learned so much, and made wonderful memories.', true],
                ['Shreen Mostafa', 'Marshmallow parent', 'Thank you Marshmallow for securing a safe place and a comfort zone for my little girl ❤️', true],
            ];

            foreach ($testimonials as $i => [$name, $relation, $quote, $featured]) {
                Testimonial::updateOrCreate(['parent_name' => $name], ['relation' => $relation, 'quote' => $quote, 'is_featured' => $featured, 'sort_order' => $i]);
            }
        }

        $schools = ['Majesty International Schools', 'Knowledge Valley International School (KVS)', 'Kipling School', 'Emerald International School (EIS)', 'SKILLS – Suad Kafafi International Language School', 'Core West College', 'Stanford Egypt Schools West Cairo', 'Winchester British International School', 'Marvel International School (MIS)', 'Notion International School (NIS)'];
        foreach ($schools as $i => $school) {
            Partner::updateOrCreate(['name' => $school], ['type' => 'school', 'sort_order' => $i]);
        }

        $faqs = [
            ['What ages do you accept?', 'We welcome children from 6 months up to school age, in six classes grouped by age. Use the class finder with your child’s birthday to see their class.', 'admissions'],
            ['How do you decide my child’s class?', 'We use your child’s age on 1 October of the school year they are joining. If they join after October, we use their age on the day they start.', 'admissions'],
            ['What are your opening hours?', 'Sunday to Thursday, 8:00 am to 4:00 pm. We are closed on Friday and Saturday. After-school care runs from 4:00 to 6:00 pm on request.', 'daily'],
            ['What language do you teach in?', 'Our main language is English. Children also have weekly French and Arabic classes.', 'learning'],
            ['What do children eat?', 'Four fresh, healthy meals a day: breakfast, a morning snack, a hot lunch and a dessert or afternoon snack.', 'daily'],
            ['Can I see the classrooms before enrolling?', 'Yes. Book a visit and see the classes live on the camera screens at reception between 10:00 am and 1:00 pm.', 'admissions'],
            ['Do you provide transport?', 'Yes, we run safe buses on two routes: inside Hadayek Al Ahram, and along Al Haram up to Maryoutia. Ask the admissions team about your area.', 'daily'],
            ['How do you keep children safe?', 'Cameras cover every class, bathroom and the garden. We have four security doors, a fire alarm system and an annual abuse-prevention workshop for the team.', 'safety'],
            ['Do you run holiday camps?', 'Yes — summer, winter, spring and autumn camps for children aged 4 to 12, with three meals included.', 'camps'],
            ['How do I enroll?', 'Fill in the enrollment form or call your nearest branch. Our admissions team will call you back, answer your questions and book your visit.', 'admissions'],
        ];
        foreach ($faqs as $i => [$question, $answer, $category]) {
            Faq::updateOrCreate(['question' => $question], compact('answer', 'category') + ['sort_order' => $i]);
        }

        $jobs = [
            ['Class Teacher', 'Full time', 'Lead a class of little learners with warmth, structure and creativity.', ['Degree in education or a related field', 'Fluent English', 'Experience with children under 6']],
            ['English Teacher', 'Full time', 'Teach phonics, reading and writing through play.', ['Excellent spoken and written English', 'Early years teaching experience']],
            ['Art Teacher', 'Part time', 'Plan daily art and craft sessions and our end-of-year exhibition.', ['Art or design background', 'Patience and creativity with young children']],
            ['Teacher Supervisor', 'Full time', 'Support and coach our teaching team and keep standards high.', ['3+ years in early childhood education', 'Leadership experience']],
            ['Internship Program (15+)', 'Internship', 'Hands-on experience working alongside our teachers, available in both branches.', ['Age 15 or above', 'Love for children and eagerness to learn']],
        ];
        foreach ($jobs as $i => [$title, $type, $description, $requirements]) {
            JobOpening::updateOrCreate(['slug' => str($title)->slug()], compact('title', 'type', 'description', 'requirements') + ['sort_order' => $i]);
        }
    }
}
