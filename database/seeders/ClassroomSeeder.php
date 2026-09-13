<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Classroom;
use Illuminate\Database\Seeder;

/**
 * Starter class & activity content. Everything here is editable from Dashboard → Classes / Activities,
 * including photos per class, per activity, and per activity inside a class.
 */
class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            // slug => [name, category, icon, color, summary]
            'english-academic' => ['English academic class', 'academic', 'abc', '#E8177F', 'Letters, numbers, shapes, colors and opposites, taught in English through songs, games and hands-on play.'],
            'topic-booklet' => ['Monthly topic booklet', 'academic', 'book', '#8479BD', 'Every month has a theme. Children practise it in their own booklet and bring their work home to show you.'],
            'storytelling' => ['Storytelling', 'languages', 'story', '#2CBCC9', 'Daily story time that grows imagination, vocabulary and empathy.'],
            'arabic' => ['Arabic class', 'languages', 'letters', '#7FA82A', 'A weekly Arabic class with letters, songs and everyday words.'],
            'french' => ['French class', 'languages', 'globe', '#2CBCC9', 'A weekly French class: greetings, colors, numbers and songs.'],
            'gymnastics' => ['Gymnastics', 'movement', 'gym', '#E8177F', 'Two sessions a week that build strength, balance, coordination and confidence.'],
            'zumba-pe' => ['Zumba & PE', 'movement', 'music', '#E8A317', 'Dance, games and physical education to burn energy and learn to move together.'],
            'motor-skills' => ['Fine & gross motor skills', 'movement', 'hands', '#8479BD', 'Threading, stacking, climbing, cutting and drawing — the building blocks of writing and sport.'],
            'sensory-play' => ['Sensory play', 'science', 'sparkle', '#E8A317', 'Magic sand, water, textures and sounds for little hands exploring the world.'],
            'science-experiments' => ['Science experiments', 'science', 'flask', '#2CBCC9', 'Hands-on experiments — volcanoes, floating and sinking, colors mixing, the solar system.'],
            'garden-time' => ['Garden time', 'science', 'leaf', '#7FA82A', 'Fresh air, planting, bugs and free play in the garden every day.'],
            'art-craft' => ['Art & crafts', 'creative', 'brush', '#E8177F', 'Painting, clay, canvas and crafts every day.'],
            'music-dance' => ['Music & dancing', 'creative', 'note', '#8479BD', 'Learning through songs, rhythm and movement.'],
            'cooking' => ['Cooking sessions', 'life', 'chef', '#E8A317', 'A weekly session making simple, healthy food together.'],
            'feelings' => ['Feelings & manners', 'life', 'heart', '#E8177F', '“How do you feel?” activities that help children name emotions, share and take turns.'],
            'self-care' => ['Self-care & potty training', 'life', 'star', '#2CBCC9', 'Gentle, step-by-step independence: washing hands, eating alone and toilet training with your family.'],
            'monthly-trip' => ['Monthly trip', 'outings', 'bus', '#7FA82A', 'A trip every month — Juhayna factory, Dolphina, Air Zone, animal farms and more.'],
            'events' => ['Events & celebrations', 'outings', 'balloon', '#8479BD', 'Science Day, Career Day, Sports Day, Ramadan, Eid, Mother’s Day, Halloween, graduation and more.'],
        ];

        $sort = 0;
        foreach ($activities as $slug => [$name, $category, $icon, $color, $summary]) {
            Activity::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'category' => $category, 'icon' => $icon, 'color' => $color,
                'summary' => $summary, 'description' => $summary, 'sort_order' => $sort++, 'is_active' => true,
            ]);
        }

        $classes = [
            [
                'name' => 'Cupcake', 'slug' => 'cupcake', 'icon' => 'cupcake', 'color' => '#E8177F',
                'min_months' => 9, 'max_months' => 24, 'age_label' => '9 months – 2 years',
                'tagline' => 'Tiny explorers taking their first big steps',
                'summary' => 'Our youngest class is all about feeling safe, loved and curious. Babies and young toddlers explore through their senses, hear English every day and start their first words, steps and friendships.',
                'goals' => ['Settle in happily and build trust with their teachers', 'First words, gestures and songs in English', 'Crawling, walking, grasping and stacking', 'Healthy sleep and meal routines'],
                'routine' => [['7:00', 'Arrival & free play'], ['8:30', 'Breakfast'], ['9:30', 'Sensory play'], ['10:30', 'Songs & story'], ['11:00', 'Snack & nap'], ['12:30', 'Lunch'], ['1:30', 'Garden time'], ['2:30', 'Dessert & quiet play'], ['4:00', 'Home time']],
                'ratio' => '1 teacher : 4 children',
                'activities' => [
                    'sensory-play' => ['Daily', 'Magic sand, water trays, soft textures and sound toys.'],
                    'music-dance' => ['Daily', 'Nursery rhymes, clapping games and gentle movement.'],
                    'storytelling' => ['Daily', 'Picture books and puppet stories.'],
                    'motor-skills' => ['Daily', 'Crawling tunnels, stacking cups and grasping games.'],
                    'english-academic' => ['Daily', 'First words, animal sounds and colors through songs.'],
                    'garden-time' => ['Daily', 'Supervised outdoor play in the shade.'],
                    'art-craft' => ['3 times a week', 'Finger painting and messy play.'],
                    'events' => ['Monthly', 'Celebrations with the whole nursery.'],
                ],
            ],
            [
                'name' => 'Popcorn', 'slug' => 'popcorn', 'icon' => 'popcorn', 'color' => '#E8A317',
                'min_months' => 24, 'max_months' => 30, 'age_label' => '2 – 2.5 years',
                'tagline' => 'Busy toddlers bursting with energy',
                'summary' => 'Two-year-olds want to do everything themselves. Popcorn gives them a structured, joyful day with lots of movement, early English vocabulary and the first steps towards independence.',
                'goals' => ['Two- and three-word sentences in English', 'Colors, animals and body parts', 'Starting potty training with your family', 'Playing alongside and then with friends'],
                'routine' => [['7:00', 'Arrival & free play'], ['8:30', 'Breakfast'], ['9:15', 'Circle time'], ['9:45', 'English & motor play'], ['10:45', 'Snack'], ['11:00', 'Gymnastics or Zumba'], ['12:30', 'Lunch & rest'], ['2:00', 'Art or garden'], ['2:45', 'Dessert & story'], ['4:00', 'Home time']],
                'ratio' => '1 teacher : 6 children',
                'activities' => [
                    'english-academic' => ['Daily', 'Colors, animals, body parts and simple action words.'],
                    'motor-skills' => ['Daily', 'Climbing, ball games, big crayons and threading beads.'],
                    'gymnastics' => ['Twice a week', 'Rolling, balancing and jumping on soft mats.'],
                    'zumba-pe' => ['Weekly', 'Dance games to burn energy.'],
                    'sensory-play' => ['3 times a week', 'Clay, magic sand and sorting colors.'],
                    'art-craft' => ['Daily', 'Painting, stickers and simple crafts.'],
                    'storytelling' => ['Daily', 'Short stories with props and puppets.'],
                    'self-care' => ['Daily', 'Handwashing, eating independently and potty training.'],
                    'garden-time' => ['Daily', 'Outdoor play and planting.'],
                    'events' => ['Monthly', 'Celebrations and theme days.'],
                ],
            ],
            [
                'name' => 'Candy', 'slug' => 'candy', 'icon' => 'candy', 'color' => '#2CBCC9',
                'min_months' => 30, 'max_months' => 36, 'age_label' => '2.5 – 3 years',
                'tagline' => 'Little talkers with big questions',
                'summary' => 'Candy children are ready for more structure. They start their monthly topic booklet, meet French and Arabic for the first time and learn to share, take turns and name their feelings.',
                'goals' => ['Recognising shapes, colors and numbers 1–10', 'Following two-step instructions in English', 'First French and Arabic words', 'Fully toilet trained by the end of the year'],
                'routine' => [['7:00', 'Arrival & free play'], ['8:30', 'Breakfast'], ['9:00', 'Circle time'], ['9:30', 'English academic class'], ['10:30', 'Snack'], ['10:45', 'Gymnastics, language or science'], ['12:30', 'Lunch'], ['1:15', 'Art & crafts'], ['2:30', 'Dessert & story'], ['4:00', 'Home time']],
                'ratio' => '1 teacher : 8 children',
                'activities' => [
                    'english-academic' => ['Daily', 'Shapes, colors, numbers 1–10 and opposites.'],
                    'topic-booklet' => ['Weekly', 'Their first booklet on the monthly topic.'],
                    'french' => ['Weekly', 'Greetings, colors and songs.'],
                    'arabic' => ['Weekly', 'Letters through songs and pictures.'],
                    'gymnastics' => ['Twice a week', 'Balance beams, rolls and jumps.'],
                    'science-experiments' => ['Weekly', 'Colors mixing, floating and sinking.'],
                    'art-craft' => ['Daily', 'Clay, canvas painting and crafts.'],
                    'feelings' => ['Weekly', '“How do you feel?” and sharing games.'],
                    'self-care' => ['Daily', 'Completing toilet training and dressing skills.'],
                    'storytelling' => ['Daily', 'Stories they help to tell.'],
                    'events' => ['Monthly', 'Celebrations and theme days.'],
                ],
            ],
            [
                'name' => 'Ice Cream', 'slug' => 'ice-cream', 'icon' => 'icecream', 'color' => '#8479BD',
                'min_months' => 36, 'max_months' => 42, 'age_label' => '3 – 3.5 years',
                'tagline' => 'Curious minds getting ready to learn',
                'summary' => 'At three, children love to ask “why?”. Ice Cream adds weekly cooking and a trip every month to a full academic day, so learning always connects to the real world.',
                'goals' => ['Letter sounds A–Z and numbers to 20', 'Holding a pencil and tracing lines', 'Speaking in full sentences in English', 'Working in small groups'],
                'routine' => [['7:00', 'Arrival & free play'], ['8:30', 'Breakfast'], ['9:00', 'Circle time'], ['9:30', 'English academic class'], ['10:30', 'Snack'], ['10:45', 'Languages or science'], ['11:45', 'Gymnastics or Zumba'], ['12:30', 'Lunch'], ['1:15', 'Booklet & crafts'], ['2:30', 'Dessert & story'], ['4:00', 'Home time']],
                'ratio' => '1 teacher : 10 children',
                'activities' => [
                    'english-academic' => ['Daily', 'Letter sounds, numbers to 20, shapes and opposites.'],
                    'topic-booklet' => ['Twice a week', 'Tracing, matching and coloring on the monthly topic.'],
                    'french' => ['Weekly', 'Everyday words and songs.'],
                    'arabic' => ['Weekly', 'Letters and short words.'],
                    'gymnastics' => ['Twice a week', 'Strength, balance and coordination.'],
                    'zumba-pe' => ['Weekly', 'Dance and team games.'],
                    'science-experiments' => ['Weekly', 'Volcanoes, magnets and plants.'],
                    'cooking' => ['Weekly', 'Simple healthy snacks.'],
                    'art-craft' => ['Daily', 'Clay, canvas and crafts.'],
                    'music-dance' => ['Weekly', 'Songs and rhythm.'],
                    'monthly-trip' => ['Monthly', 'A trip linked to the month’s topic.'],
                    'events' => ['Monthly', 'Career Day, Science Day and celebrations.'],
                ],
            ],
            [
                'name' => 'Lollipop', 'slug' => 'lollipop', 'icon' => 'lollipop', 'color' => '#7FA82A',
                'min_months' => 42, 'max_months' => 48, 'age_label' => '3.5 – 4 years',
                'tagline' => 'Confident learners finding their voice',
                'summary' => 'Lollipop children read their first words, write their names and present their science projects to friends. The day is busy, varied and designed to build confidence.',
                'goals' => ['Blending sounds into simple words', 'Writing their name and numbers', 'Counting, sorting and simple patterns', 'Presenting a small project to the class'],
                'routine' => [['7:00', 'Arrival & free play'], ['8:30', 'Breakfast'], ['9:00', 'Circle time'], ['9:20', 'English academic class'], ['10:30', 'Snack'], ['10:45', 'Languages'], ['11:30', 'Gymnastics or science'], ['12:30', 'Lunch'], ['1:15', 'Booklet, cooking or crafts'], ['2:30', 'Dessert & story'], ['4:00', 'Home time']],
                'ratio' => '1 teacher : 12 children',
                'activities' => [
                    'english-academic' => ['Daily', 'Phonics, first words, writing and numbers.'],
                    'topic-booklet' => ['3 times a week', 'Writing and problem-solving on the monthly topic.'],
                    'french' => ['Weekly', 'Short conversations and songs.'],
                    'arabic' => ['Weekly', 'Letters, words and writing practice.'],
                    'gymnastics' => ['Twice a week', 'Routines that build strength and confidence.'],
                    'zumba-pe' => ['Weekly', 'Team games and sports.'],
                    'science-experiments' => ['Weekly', 'Experiments and small projects like the solar system.'],
                    'cooking' => ['Weekly', 'Healthy food making.'],
                    'art-craft' => ['Daily', 'Canvas, clay and craft projects.'],
                    'feelings' => ['Weekly', 'Manners, empathy and problem-solving with friends.'],
                    'monthly-trip' => ['Monthly', 'Juhayna factory, Dolphina, Air Zone and more.'],
                    'events' => ['Monthly', 'Sports Day, Career Day and celebrations.'],
                ],
            ],
            [
                'name' => 'Cotton Candy', 'slug' => 'cotton-candy', 'icon' => 'cottoncandy', 'color' => '#C0479A',
                'min_months' => 48, 'max_months' => 72, 'age_label' => '4 years – school age',
                'tagline' => 'Big kids ready for big school',
                'summary' => 'Our oldest class prepares children for international school interviews and KG1. Reading, writing, maths and three languages, plus the famous graduation ceremony at the end of the year.',
                'goals' => ['Reading short sentences in English', 'Adding and subtracting to 10', 'Ready for international school assessments', 'Independent, confident and kind'],
                'routine' => [['7:00', 'Arrival & free play'], ['8:30', 'Breakfast'], ['9:00', 'Circle time'], ['9:15', 'English reading & writing'], ['10:15', 'Maths'], ['10:45', 'Snack'], ['11:00', 'Languages'], ['11:45', 'Gymnastics or science'], ['12:30', 'Lunch'], ['1:15', 'Projects, cooking or crafts'], ['2:30', 'Dessert & story'], ['4:00', 'Home time']],
                'ratio' => '1 teacher : 12 children',
                'activities' => [
                    'english-academic' => ['Daily', 'Reading, writing, maths and school-readiness skills.'],
                    'topic-booklet' => ['Daily', 'Worksheets and projects on the monthly topic.'],
                    'french' => ['Weekly', 'Vocabulary, songs and short dialogues.'],
                    'arabic' => ['Twice a week', 'Reading and writing Arabic letters and words.'],
                    'gymnastics' => ['Twice a week', 'Gymnastics routines and a show for parents.'],
                    'zumba-pe' => ['Weekly', 'Sports and teamwork.'],
                    'science-experiments' => ['Weekly', 'Experiments, projects and Science Day presentations.'],
                    'cooking' => ['Weekly', 'Following simple recipes.'],
                    'art-craft' => ['Daily', 'Art projects for the end-of-year exhibition.'],
                    'storytelling' => ['Daily', 'Retelling stories and acting them out.'],
                    'monthly-trip' => ['Monthly', 'Trips linked to the topic of the month.'],
                    'events' => ['Monthly', 'Graduation ceremony, Schools Expo and celebrations.'],
                ],
            ],
        ];

        foreach ($classes as $i => $data) {
            $classroom = Classroom::updateOrCreate(['slug' => $data['slug']], [
                'name' => $data['name'],
                'icon' => $data['icon'],
                'color' => $data['color'],
                'min_months' => $data['min_months'],
                'max_months' => $data['max_months'],
                'age_label' => $data['age_label'],
                'tagline' => $data['tagline'],
                'summary' => $data['summary'],
                'description' => $data['summary'],
                'goals' => $data['goals'],
                'daily_routine' => collect($data['routine'])->map(fn ($r) => ['time' => $r[0], 'label' => $r[1]])->all(),
                'teacher_ratio' => $data['ratio'],
                'sort_order' => $i,
                'is_active' => true,
            ]);

            $sync = [];
            $order = 0;
            foreach ($data['activities'] as $slug => [$frequency, $details]) {
                $sync[Activity::where('slug', $slug)->value('id')] = [
                    'frequency' => $frequency, 'details' => $details, 'sort_order' => $order++,
                ];
            }
            $classroom->activities()->sync($sync);
        }
    }
}
