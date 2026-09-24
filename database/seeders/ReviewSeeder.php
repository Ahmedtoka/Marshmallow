<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * The parent reviews from the nursery's Facebook page, with the profile pictures already stored in
 * public/media. Generated from the local site after `php artisan reviews:import`; reviews are
 * matched on their Facebook link, so re-running updates instead of duplicating.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::REVIEWS as $review) {
            $key = $review['source_url']
                ? ['source_url' => $review['source_url']]
                : ['parent_name' => $review['parent_name'], 'quote' => $review['quote']];

            Testimonial::updateOrCreate($key, $review);
        }
    }

    private const REVIEWS =     [
      0 => 
      [
        'parent_name' => 'A Marshmallow mom',
        'relation' => 'Parent of a graduate',
        'quote' => 'Marshmallow, the best memories ever, thanks for everything… Nehal Fahmy is the best person ever in my daughter’s life. I am grateful for your presence in our lives.',
        'rating' => 5,
        'photo' => NULL,
        'source' => 'manual',
        'source_url' => NULL,
        'reviewed_at' => NULL,
        'is_visible' => false,
        'is_featured' => false,
        'sort_order' => 2,
      ],
      1 => 
      [
        'parent_name' => 'Hadeer Raafat',
        'relation' => NULL,
        'quote' => 'We are so grateful for all the love, care, and support you gave our son throughout his time at your nursery. ❤️
        He truly enjoyed every day, learned so much, and made wonderful memories. Thank you for creating such a happy and caring environment. We will always appreciate everything you have done. Wishing you continued success 🥰🥰',
        'rating' => 5,
        'photo' => 'uploads/reviews/u54i8qbN607loNWUJcAcGJiH.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/hadeer.rafaat.54/posts/pfbid02THeePpMq9sXWfpes5hjBXndTk4nCx1VfhAjaLUy1esLZCij1eL9bGNGrDgEq2DDvl',
        'reviewed_at' => '2026-08-28',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 3,
      ],
      2 => 
      [
        'parent_name' => 'Shreen Mostafa',
        'relation' => NULL,
        'quote' => 'Thank you marshmallow for securing a safe place and a comfort zone for my little girl ❤️❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/3lJWPhYcd2H5tuzZcS2Yarfm.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/drshreen95/posts/pfbid0TvtfQ9d9AQ6bRfS8dxEaq3Dty1XCBbjaQgzARrY6TYtPZUa91TMD7v4G9FDy4Hkml',
        'reviewed_at' => '2026-08-23',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 4,
      ],
      3 => 
      [
        'parent_name' => 'Marwa Reda',
        'relation' => NULL,
        'quote' => 'Thank you, Marshmallow, for 2 years and 7 months of love, care, and beautiful memories. 🤍
        Truly the best choice we could have made for Layan. 🥹✨
        Thank you to the whole team, and a very special thank you to Miss Sama & Miss Sally for all the love and care you’ve given her. 
        You’ll always have a special place in our hearts. 🤍
        Forever grateful. ❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/dvh9en8G4UueYysJfUpbU5yi.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/marwa.reda.920515/posts/pfbid0ZMJovs9Pmwj3qvXLvhTkgACQxZVxsPxk9qeTvviFs5nz3Ti7U4z7M6CRT5UX8Netl',
        'reviewed_at' => '2026-08-16',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 5,
      ],
      4 => 
      [
        'parent_name' => 'Basant Mahfouz',
        'relation' => NULL,
        'quote' => 'We\'ve been with them in zayed branch since 2021 with my older daughter and now my son,, i cant recommend them enough. They are very kind and caring even the nannies are lovely. Education wise, theyre great and i think helped my son get passed his age in the knowledge he should have ❤️ we love them ❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/Jm4QvWxTd7j7g8KVWpEFiG17.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/basant.mahfouz.7/posts/pfbid0jQi1SHJMHcbFAWDjjTr7PS7U93JCeHched6axQqEH3Akf6kMmMYxTQfEASR5vjfLl',
        'reviewed_at' => '2024-10-31',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 6,
      ],
      5 => 
      [
        'parent_name' => 'Wafaa Abd Elhamid',
        'relation' => NULL,
        'quote' => 'شكرا ليكو على كل يوم الحسن فيه كان بيرجع مبسوط شكرا لكل الدعم والثقه والتغيير للافضل في كل حاجه',
        'rating' => 5,
        'photo' => 'uploads/reviews/F2MtLkMVrsnL9zyQWb2D3ydJ.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/wafaa.abdelhamid/posts/pfbid05n9J5xvRpWd6ndHBF1WJiHzFL6zDaPyCmRSps9VFkZXQ1BMsxbWbzDPRN9NfbpkFl',
        'reviewed_at' => '2024-09-11',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 7,
      ],
      6 => 
      [
        'parent_name' => 'Eman Abdrabuhu',
        'relation' => NULL,
        'quote' => 'My lovely nursery ❤️
        المكان الوحيد ال حسيت بأمان فيه علي ابني وانبهرت اما دخلنا انترفيوا المدرسه ولقيته بتجاوب معاهم وكل المدرسات فرحانين بيه دعيت لكم كتير كلكم هنفتقدكم كتير بجد  وهتوحشونا اوي بحبك يا دعاء وميس ندا وميس هدير وميس نورا ورغوده ال اخدت ابني وهو سنه حنيتها ما تتوصفش الف شكر ليكم كلكم فرد فرد والله الله يوفقكم ويكتب لكم الخير يارب 💚💚💚💚',
        'rating' => 5,
        'photo' => 'uploads/reviews/wUR0bOmZwwahiOKDyaxFQc0u.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/eman.abdrabuhu/posts/pfbid0asz9LCMHmqmBADUgynPXiEWVTJe3rgr8YWPNmGFU8GYZbCkvdfbTkZZSHPR2BYEKl',
        'reviewed_at' => '2024-09-10',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 8,
      ],
      7 => 
      [
        'parent_name' => 'Soheir Azzam',
        'relation' => NULL,
        'quote' => '“We’ve had a wonderful experience with this nursery. Everything has been perfect, from the caring and attentive staff to the well-organized environment. The activities are engaging and age-appropriate, and my son is always excited to attend. I highly recommend this nursery to any parent looking for a safe, nurturing, and enriching space for their child.”',
        'rating' => 5,
        'photo' => 'uploads/reviews/F0MzhgnY2NhOO6cC2jgbzPVa.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/zahret.lotus.7/posts/pfbid029xommqQPPREMq25A2Y7Byxc95eJZTshj213rnGcXiUnTVo1PjW9QPwg68TXbAY6Rl',
        'reviewed_at' => '2024-09-09',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 9,
      ],
      8 => 
      [
        'parent_name' => 'Mariam Y El-Shebokshey',
        'relation' => NULL,
        'quote' => 'As a parent, it\'s truly heartwarming to know that my daughter has been in such capable and caring hands. The team at Marshmallow nursery has created a safe and nurturing space that has become a second home for my daughter. 
        I would like to extend my deepest thanks to each and every staff member at Marshmallow, specially Miss Rowaida and Miss Hadeel, Walaa and special thanks to the wonderful manager, Miss Doaa. It is clear that you all treating our children as moms before being teachers, and that makes all the difference!!
        I highly recommend Marshmallow to any parent seeking a nurturing and enriching environment for their child. Thank you once again for being an exceptional team and for taking such wonderful care of Sophia.  ❤',
        'rating' => 5,
        'photo' => 'uploads/reviews/Gz8TCDXHqarP68hxScuzMvPK.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/mariam.yusufelshebokshey/posts/pfbid02WF7HxmetrWYpKdGKrLYJXLnMGJUJeP5rwERgJkWoCKBFMmFSRkaMjFFx8zX5VznCl',
        'reviewed_at' => '2023-09-05',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 10,
      ],
      9 => 
      [
        'parent_name' => 'Israa Mamdouh',
        'relation' => NULL,
        'quote' => 'شكرا اقل كلمه ممكن تتقال ليكوا❤️شكرا انكوا كنتوا ديما عند حسن ظني ❤️شكرا انكوا ساعدتوني اني ابقي مطمنه علي كنده و هي لسه عندها سنه❤️شكرا انكوا كنتوا البيت التاني لينا❤️شكرا ان كنده كل يوم كانت بترجع فرحانه و مبسوطه و بتتعلم حاجه جديده❤️شكرا لكل واحد فيكوا بجد❤️
        Special thanks for the awesome graduation party🤩it was just an amazing day 🤗full with love,honor,tears 🥹you rocked 
        You deserve to be the best nursery and best team.you deserve to be number one ☝️',
        'rating' => 5,
        'photo' => 'uploads/reviews/HIPnBqyMdahHdJ7xyNwkvDQs.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/israa.mamdouh1/posts/pfbid0YLLMDRb6TRqNrty4DgM2CceYKMrk4VNE4K7v4hY8iYSdwe6UYRiyHYQLb7xtvxg9l',
        'reviewed_at' => '2023-07-10',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 11,
      ],
      10 => 
      [
        'parent_name' => 'Shereen Lotfy',
        'relation' => NULL,
        'quote' => 'بنشكركم علي مجهودكم الكبير النهاردة في الحفلة والشكل المشرف اللي ظهر بيه الولاد 
        حقيقي فرحتونا اوووي وتسلم ايديكم ❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/9nCo4znjfRj26rTgpHdDMmaN.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/shereen.lotfy.33110/posts/pfbid02AwNuW3zk86ibPSaAQRNSNX31rJF46rEfXy9vCxVueq1h1A5nQyj6Q6DvKEgjKKZ8l',
        'reviewed_at' => '2023-06-24',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 12,
      ],
      11 => 
      [
        'parent_name' => 'Niveen Zakaria',
        'relation' => NULL,
        'quote' => 'بجد احلى Nursery❤️ شكرا ليكم بجد 
         يونس معاكم كان مبسوط و كنتوا معاه في كل حاجه و بتخله ديما يتجاوز اي مخاوف و يعرف يتبسط و شكرا على الحفله الفظيعه دي بجد كان يوم حلو اوووى و يونس راجع مبسوط عايز حفله تاني 😄 
        شكرا لكل  \'teachers على مجهودهم الرائع و على انهم كانو طول الوقت معايا  في اي حاجه بحتاجها بخصوص يونس ❤️بنحبكم اوووى 🥰',
        'rating' => 5,
        'photo' => 'uploads/reviews/Uaxi4TUWZKkxtsdrSnkKxFA2.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/niveen.zakaria.3/posts/pfbid0joXrSe3FKNRgvsXjZfdZLoJfCMiCBHSYwLEcohphrRjcMfiLZAcFf5Y9zGSzVJoSl',
        'reviewed_at' => '2023-06-24',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 13,
      ],
      12 => 
      [
        'parent_name' => 'SaRa Elkady',
        'relation' => NULL,
        'quote' => 'It has been a great and lovely journey, we have mixed feelings that Tiya is leaving the nursery heading to the school, we really want to thank each and everyone at Marshmello nursery, Tiya wouldn’t progress and shine without your continues efforts and support, YOU ARE THE BEST❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/LnPBZ42rSDapyNCZhPKrcAYH.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/sara.elkady.908/posts/pfbid0wzn3V7hjSStW85ADN3epsZ6R5y8h1JyfAwKJLswajrMkYZ3SFvdd5pMwkgAnfd9Yl',
        'reviewed_at' => '2021-08-13',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 14,
      ],
      13 => 
      [
        'parent_name' => 'Heba Ahmed',
        'relation' => NULL,
        'quote' => 'And its time to thank all marshmallow staff , No words can describe how much selim\'s love u all  
        شكرا ع كل يوم سليم رجع من الحضانه مبسوط 
        شكرا لكل الميسز و كل الكير
        ❤️❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/hMRU87tJSJhtViQw03OlzmBJ.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/heba.ahmed.9889/posts/pfbid02fxxkFcdVhtN1nLye4G3fGS7vjFK3qzCeCFvb9z341X6eK6NGEQHahQWhSetsrYEil',
        'reviewed_at' => '2021-08-13',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 15,
      ],
      14 => 
      [
        'parent_name' => 'Eman Hany Swailam',
        'relation' => NULL,
        'quote' => 'من احسن الحضانات اللي اتعاملت معاهم من  اداره لمدرسين ل كل الناس اللي فيها بجد قمه ف الاحترام والذوق ❤️ وكفايه تعبكوا في انكوا تبسطونا كل حاجه بتوصل بمنتهي الحب 😍
        بيري بتحبكوا اوي وشويه وهتبقي شيري كمان ❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/OQSnY54xcOXs9AOM960hWKAo.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/emma.swailam/posts/pfbid0iRvENtqYXxXEKwhcdBopJ6k6U82g8x4wW9FNzZPQ55LgEywJKde5ipb2dtRNNV1ol',
        'reviewed_at' => '2021-03-30',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 16,
      ],
      15 => 
      [
        'parent_name' => 'Passant Abu-Gaafar',
        'relation' => NULL,
        'quote' => 'The best nursery in the area. Extremely caring and amazing staff. My daughter started getting excited for nursery after only 1 week of going which is a huge accomplishment and it truly means that she’s enjoying her time. She go in with a smile and leave with a laugh and that’s makes me feel confident and safe about the place.
        Thank you Marshmallow for the super quality of education and entertainment you provide for my daughter ❤️Camellia loves you all so much ❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/NN72AGVC1zt8i8jJqifkUwzp.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/passanty/posts/pfbid0soZoymudxXGs4ouBe9JtKKvgdHqZmBRaBYEWGxhBad2VZFW8uvtvBH1mspiFGqDzl',
        'reviewed_at' => '2021-03-30',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 17,
      ],
      16 => 
      [
        'parent_name' => 'Sarah Awaad',
        'relation' => NULL,
        'quote' => 'The best nursery in the area. Extremely caring and amazing staff. My kids started getting excited for nursery after only 1 week of going which is a huge accomplishment and it truly means that they enjoy their time. They go in with a smile and leave with a laugh and that just warms my heart and make me feel confident and safe about the place they spend their day in and the people they spend it with. Thank you Marshmallow for the superb quality of education and entertainment you provide for my kids ❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/NFiFESd9NwF11bEtkSTIwF3A.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/sarahawaad/posts/pfbid022kXs4rQfa67VCRaUdZciEpHaXj2QYGPhuDZQH5fuZdhZxJ1c1QwdoYoc9xYCsPkal',
        'reviewed_at' => '2021-03-29',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 18,
      ],
      17 => 
      [
        'parent_name' => 'Ahmed ElNakib',
        'relation' => NULL,
        'quote' => 'wonderful and fantastic nursery.',
        'rating' => 5,
        'photo' => 'uploads/reviews/PWIu0Hhd4UtTM4PWwQPGJGRf.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/aelnakib/posts/pfbid02zm6ZyQkirSfiFy3VbUgaym8mVD6hFLpGLsBVudxZbeRgHMDJqm4WLCG4L7tQNwiyl',
        'reviewed_at' => '2020-02-21',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 19,
      ],
      18 => 
      [
        'parent_name' => 'Marina Girges',
        'relation' => NULL,
        'quote' => 'Thanks for raising up my kid. It was fruitful period full of activities and learning lessons.
        Thanks for the teaching team, care staff, administration team and ofcourse Miss Nehal. 
        Wish you the best in the future, and more successful stories.',
        'rating' => 5,
        'photo' => 'uploads/reviews/UMob2yrGHxjL03LkseBUHePP.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/marina.girges.5/posts/pfbid0363DHyVH1575XZoCVv9JhyJyXp3ay8GMJYw1ZSeAMU4fevPVmRp21Cu72vKDusjJPl',
        'reviewed_at' => '2019-08-29',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 20,
      ],
      19 => 
      [
        'parent_name' => 'Heba Sabry',
        'relation' => NULL,
        'quote' => 'After spending 3 .5 year at Marshmelow nursery I can say it is the best place ever 
        Where our children grow and learn.she totally changed to the best . She become 
        More powerful ،more confidence and more social character. 
        Staff  are friendly and helpful 
        Thanks a lot for your care and love 
        I highly recommend Marshmelow to every parents ❤️
        Celine will miss u so much ❤️❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/oxnz3zfOYuSW05ul1765oRU0.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/heba.sabry.5076/posts/pfbid032gi7CY6PeNAj2f1BDw1TVmxkeoukG6ZBPSQ3iKooZHkJFjeQC1hWETNXNBR6aGwQl',
        'reviewed_at' => '2019-08-28',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 21,
      ],
      20 => 
      [
        'parent_name' => 'Aya Sami Beleity',
        'relation' => NULL,
        'quote' => 'Amazing Nursery 😍 Definitely  a safe second home for my daughter 😍',
        'rating' => 5,
        'photo' => 'uploads/reviews/lSiqfutA1wLK5YHD9VvRyTNH.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/aya.beleity/posts/pfbid02sua8ZhX5b6Gec4h9FVxPEvfcmbzQ8LL6W2iWR6vBxzigh7zHewx8ZYxBca3k2o4xl',
        'reviewed_at' => '2019-08-28',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 22,
      ],
      21 => 
      [
        'parent_name' => 'Ahmed Hteta',
        'relation' => NULL,
        'quote' => 'Amazing nursery 
        Amazing staff',
        'rating' => 5,
        'photo' => 'uploads/reviews/NiO9rXLzV27inGNU19N2YcSG.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/smartleader/posts/pfbid0TrddUFBvxRJjtstUT9GaTdC8GfiEPK1Jj3Z3c1McC9R6TDi2YWwCDx52tfDLja9Wl',
        'reviewed_at' => '2018-09-12',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 23,
      ],
      22 => 
      [
        'parent_name' => 'Ali Osman',
        'relation' => NULL,
        'quote' => 'Best nursery in Egypt♥️♥️♥️♥️♥️',
        'rating' => 5,
        'photo' => 'uploads/reviews/gD4jxfp8MOPvgTZ574Ysv9ke.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/ali.bofon.3/posts/pfbid0KMSUAESpH433MnXVyJSioYTqzLYkfz1z8WKfHoijepTMU3Emo9PRrjfzwb9Rk3WWl',
        'reviewed_at' => '2018-09-08',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 24,
      ],
      23 => 
      [
        'parent_name' => 'Ehab Ragab',
        'relation' => NULL,
        'quote' => 'ناس محترمه ومحترفه وخبرة في التعامل مع الأطفال',
        'rating' => 5,
        'photo' => 'uploads/reviews/jCtuMcMefYpUgpJ373Z0bWll.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/ehab.ragab.673240/posts/pfbid0Rq1iByLf6pYPwVxeMpeYodjHgrcv8dKV5uR7mySGhUEp5mtin3AJEfoeW1F3DVTGl',
        'reviewed_at' => '2018-09-05',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 25,
      ],
      24 => 
      [
        'parent_name' => 'Doaa Youssef',
        'relation' => NULL,
        'quote' => 'I love the place and the stuff thank you for everything with my Jamila ❤️ you are the best keep it up 😍😘❤️🌸🌸🌸🌸',
        'rating' => 5,
        'photo' => 'uploads/reviews/hoy6Gc3hivCeZxEwx3bFeEXQ.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/doaa.youssef.547/posts/pfbid0chGJMZv2imqg3dFRsZGzL4km2MnGKxrMhLh9WeZwpNfsVa5EcemHAEXwHow4J7C3l',
        'reviewed_at' => '2018-09-03',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 26,
      ],
      25 => 
      [
        'parent_name' => 'Dina Samir Sourial',
        'relation' => NULL,
        'quote' => 'The Sun and hot weather REALLY APROOVES how u love and care for our kids to simply complete the ceremony successfully under any circumstances, very helpful team work, family spirit .  Decoration, shows animation and all of all U R TO7FA. Special thanks to Ms TASNEEM from Sara. LUV u all Marshmallow\'s',
        'rating' => 5,
        'photo' => 'uploads/reviews/zOnot5bTxMf3yvIp6b9bOSFY.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/dina.s.sourial/posts/pfbid02jxGXbdX49xhLyKM4mWGkcyG5EQ8cB4CiXqRVizKyQ73JDQXh3q9BSbAp6Fb8ciSHl',
        'reviewed_at' => '2018-09-03',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 27,
      ],
      26 => 
      [
        'parent_name' => 'Jasmin Elsherif',
        'relation' => NULL,
        'quote' => 'شكرا بجد اسرة مارمشيلو علي حفله التخرج الحلوة وبجد اتبسطنا جدا جدا وابراهيم مبسوط من مجهوداتكم وتعبكم في اظهار الحفله الجميله جدا ومهما اتكلم مش حعرف اعبر عن فرحتي بيكم وبابني ونفس الوقت زعلانه علشان سيباكم عشرة اربع سنين عمري ما اشتكيت ولت قلت ثقتي فيكم بالعكس كان المكان الوحيد ال بوودي ابني ليه وانا مطمنه حتوحشونا جدا جدا ♥',
        'rating' => 5,
        'photo' => 'uploads/reviews/HiGs8SVznMYK0CbBgCHxX5hV.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/jasmin.elsherif/posts/pfbid02LUXZQZjEKCbGkUgVbc295d3P1WaZvsK3JSQ9xQmFSKz6UvCsc1UfgZyJ1bRkMmHNl',
        'reviewed_at' => '2018-09-02',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 28,
      ],
      27 => 
      [
        'parent_name' => 'Hala Tayel',
        'relation' => NULL,
        'quote' => '“Best graduation party “ l want to say thank u to everyone in marshmallow nursery, Layan enjoyed her time and learnt a lot in marshmallow, the staff is very friendly 😍 it has a unique approach in teaching and dealing with the children, The administration exerted excellent efforts and are very welcoming to parents... really we love all of u in marshmallow nursery ❤️❤️',
        'rating' => 5,
        'photo' => 'uploads/reviews/9bjyvDtcvLX1qFliLLF3xgis.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/hala.tayel.81948/posts/pfbid0SQ6yBP2NQ1B6kbwPzWEwuTpgdWsvAv5Vbf3p2Lax9mqTwZs5598XXHKqG1KFza4yl',
        'reviewed_at' => '2018-09-02',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 29,
      ],
      28 => 
      [
        'parent_name' => 'Karim Kamal',
        'relation' => NULL,
        'quote' => 'The most great nursery in the world',
        'rating' => 5,
        'photo' => 'uploads/reviews/K4WzxwNjdJoM2216c8q4fbjI.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/karim.kamal.96/posts/pfbid025oZabNqLTar98DNz2k4kgPJBQySVBBbpw3DLfo3V4iRa7TvFY7rFoyiKvYRb6vBEl',
        'reviewed_at' => '2018-05-27',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 30,
      ],
      29 => 
      [
        'parent_name' => 'Manar Said',
        'relation' => NULL,
        'quote' => 'Today is Laila’s second day, and now i can admit that i made a right decision for choosing this place � 
        She’s so happy and um so satisfied from what i see,, and special thanks for miss Tuta for her tenderness and patience as i saw that today by myself �
        Keep it up �',
        'rating' => 5,
        'photo' => 'uploads/reviews/HJzc7LKJKjQAEASdMl7PxM8I.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/manar.said.262609/posts/pfbid03Evp4mYENsaztRzq3aSng9PrRAihLMJKCiYBoWa2qB15cg9uvazMQUQrAZBGfUP2l',
        'reviewed_at' => '2018-04-03',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 31,
      ],
      30 => 
      [
        'parent_name' => 'Om Rayan',
        'relation' => NULL,
        'quote' => 'لو سمحتي انا شوفت الصور والفيديوهات بصراحه حضانه تجنن ونفسي ابني يبقي فيها وعشان كده عوزه اعرف الاوراق المطلوبه والسعر والمكان بالظبط لو سمحتو لطفل.3سنين وشهرين وشكرا ليكم وياريت الرد هنا لان الرسايل عندي مغلقه',
        'rating' => 5,
        'photo' => 'uploads/reviews/bhG3VhuBxxiUW6XCGx62uAUl.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/shi.maa.247102/posts/pfbid02S4zRih3eW3m7gWuBVMaLRZR7mHbmtdHRT5prLc2KwJ7aetdqc9MCPtYS4y5j2HWml',
        'reviewed_at' => '2018-03-08',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 32,
      ],
      31 => 
      [
        'parent_name' => 'Noha Abd El Moneam',
        'relation' => NULL,
        'quote' => 'بجد الحضانه وهم وحفله التخرج السنه ديه تحفه وجكيله جدا وشكلهم حلو ماشاء الله  وكل الاستاف حلوين وطيبين ونونا ديه لوحدها حكايه وافكارها بتخلي الاطفال مبسوطين ���ويوسف بيحبكم مووووووووت ����',
        'rating' => 5,
        'photo' => 'uploads/reviews/CnkrFumg6PNmjVS3c3JOcCz1.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/noha.moneam/posts/pfbid0k2rd9N7kaUNcTXnEeLgFBqgNRmKvGFi8ew5mmoo8bY7DHg5TnZzPfuHuamHWqu3Fl',
        'reviewed_at' => '2017-11-01',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 33,
      ],
      32 => 
      [
        'parent_name' => 'Dosa Debo',
        'relation' => NULL,
        'quote' => 'I love it thank u marshallow avery safe place thanks����',
        'rating' => 5,
        'photo' => 'uploads/reviews/VVv6xCUeIGzE40RDZTqBGbEA.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/lozo.leza/posts/pfbid0BCER2qYFpBBHp2Y2GgFroR2efF5ztuo1KXoXKM3mB19u3f2jz14Yj2n4By1rWw15l',
        'reviewed_at' => '2017-04-13',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 34,
      ],
      33 => 
      [
        'parent_name' => 'Fatma Elsherbiny',
        'relation' => NULL,
        'quote' => 'Fayrouz\'s favorite place � 
        I\'m always positive that she is safe, enjoying her time and learning, thank you for all your efforts and for continous improvements. 
        I\'m lucky to find a nursery like Marshmallow.. We love you �',
        'rating' => 5,
        'photo' => 'uploads/reviews/7Dt8lonnpqqFdPjjTwVBPMTL.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/fatma.elsherbiny/posts/pfbid02bymDz6aQfq72o2G4PMUNgiLAGGQsqQUDukcwUizuX9SoCj49gMtvu46sM6FbdUHpl',
        'reviewed_at' => '2017-03-31',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 35,
      ],
      34 => 
      [
        'parent_name' => 'Faten Youssef',
        'relation' => NULL,
        'quote' => 'مارشيملو  حضانة  جميله  جداااا ومستوي  النظافة  فيها  عالي  وكل  اللي  فيها  ذوق  اوي  والنشاطات بتاعتها  روعه .جني بتحبكم جدا  ومبسوطه معاكم  اوي ..من نجاح لنجاح  مارشيملوووووو���',
        'rating' => 5,
        'photo' => 'uploads/reviews/g4CYInGdUtlx82wKrn29qb6K.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/totaeslam.totaeslam/posts/pfbid02BwUh9sWr1t23vWaqt9geMbbJxK5ULdanq2b5T9gjfPiaEto5U1V4VNivXPne8nFyl',
        'reviewed_at' => '2017-03-27',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 36,
      ],
      35 => 
      [
        'parent_name' => 'Nehal Ahmed Fouad',
        'relation' => NULL,
        'quote' => 'I love it ..they try to do their best to satisfy us ..thank u for what u do for our children',
        'rating' => 5,
        'photo' => 'uploads/reviews/AEgGFgu5y5j8VsAH74p7vAzm.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/nehal.a.fouad.5/posts/pfbid0mra7hYhbEjsssTx1UUeqRiJmQZb3LD1AYWEhPGhNKAU3F2ypJZMiGv8Lr3HaxUsMl',
        'reviewed_at' => '2017-03-27',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 37,
      ],
      36 => 
      [
        'parent_name' => 'Samar Mohsen',
        'relation' => NULL,
        'quote' => 'First of all i dlike to thank you for your great efforts with my daughter roza ,all teachers and nanies and especially tota�  i feel comfortable that my daughter under umberalla of marshmallow nursery , also thanks for your creativity in learning and all activites occurs since we join please keep forward best nursery ever ����',
        'rating' => 5,
        'photo' => 'uploads/reviews/YaM9s0VFiwajm1eIypUx4klo.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/samar.mohsen.71/posts/pfbid0Ey66jkPWReiE2YXnoqtjxgiYs6g5mRkCTPPPCvcAqN1anpgNJ5nLQqZi661ou1pfl',
        'reviewed_at' => '2017-03-26',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 38,
      ],
      37 => 
      [
        'parent_name' => 'Haboosh Aly',
        'relation' => NULL,
        'quote' => 'Marshmallow nursery is the best nursery ever
        I feel very comfortable because
         I feel that my daughter Farida is happy and safe there 
        She got along with all the teachers very quickly
        I wish to thank all the teachers for their efforts and their kindness',
        'rating' => 5,
        'photo' => 'uploads/reviews/pL4qv7l7hpQOsrcYFae6WyjV.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/HaboOoOoOOoOosha/posts/pfbid02V9SVDrHPTUEEWh4g67mR8TvJNmfxBhoXHxTM83cdMfoHmx5dzwBTMQKuMFnu8XGvl',
        'reviewed_at' => '2017-03-26',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 39,
      ],
      38 => 
      [
        'parent_name' => 'Roh Ghaith Alzway',
        'relation' => NULL,
        'quote' => 'مكان ممتاز وثقة ونضافة ونضام اهم شي ممتااااازين الله يوفقهم يارب',
        'rating' => 5,
        'photo' => 'uploads/reviews/vrgx31Bo4OEWU8SdFLEEQuEB.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/roh.az.3/posts/pfbid02haE7jTXvhLSgQwxqwk1EYGKzjDZfjvXauPNKxdSACqq2LVX7qqPmhTi2yCfQmaLBl',
        'reviewed_at' => '2017-03-26',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 40,
      ],
      39 => 
      [
        'parent_name' => 'Hanan El Messery',
        'relation' => NULL,
        'quote' => 'Marshmallow it\'s not just a nursery it\'s a second home to my daughter..she learn alot and enjoy her time there.... she love her teachers and staff so much...Thank you for your big effort.
        Thank you Marshmallow.',
        'rating' => 5,
        'photo' => 'uploads/reviews/vYfaMEoITiSz0uz2sRx6Sjet.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/hanan.elmessery/posts/pfbid0VXiinR9aGxyymYVsGEpaFSuBXFPWKjgTgQVUSrniyqS9hjLym65D9ePJn1dApMPpl',
        'reviewed_at' => '2017-03-26',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 41,
      ],
      40 => 
      [
        'parent_name' => 'Marwa Soliman',
        'relation' => NULL,
        'quote' => 'Isn\'t great to feel that your child is in good hands ?! You are very careful and concerned about children .you look after them very well,take care of their details ,understand their needs ... You give the child all what it should be given ;education, entertainment, and good ethics',
        'rating' => 5,
        'photo' => 'uploads/reviews/cNszxxZRoLoc2Yz4lEyGA0lD.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/marwa.soliman.9465/posts/pfbid02giY4ZF8UHYRJFxYBaMLLkrwQJiR5r8ZwcAbUZgdbWX1xwjo8r7u1z8329qeCaF1el',
        'reviewed_at' => '2017-03-26',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 42,
      ],
      41 => 
      [
        'parent_name' => 'Nora Tawfek',
        'relation' => NULL,
        'quote' => 'A big THANKS for you marshmallow nursery for your always caring ,you are not only Malika\'s nursery...you are her second home � ..the only place i feel safe&free to leave luka there thanks again for your love � Really you are a perfect home � ����',
        'rating' => 5,
        'photo' => 'uploads/reviews/VN50U2nXoTA9rCdg9MZtRDi2.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/nora.tawfek.7/posts/pfbid0WeotwCyFapRd8w9TNbJ9xU7mXym3unXY4oGLmt5Ugv3GnWHhYbnhMrDCENVefjBSl',
        'reviewed_at' => '2017-03-22',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 43,
      ],
      42 => 
      [
        'parent_name' => 'Hourya Mohamed',
        'relation' => NULL,
        'quote' => 'E\' un asilo  eccellente,Tutto perfetto!  masha Allah',
        'rating' => 5,
        'photo' => 'uploads/reviews/PA17zgOUkhA23HxAD4GvSZzg.jpg',
        'source' => 'facebook',
        'source_url' => 'https://www.facebook.com/hourya.mohamed.2025/posts/pfbid02rWDBowt5nupdZxkfjP165t4dhVWFwR68T2KNAfGSyM69YR8Sx27YLjMZwZiEUTcol',
        'reviewed_at' => '2016-11-20',
        'is_visible' => true,
        'is_featured' => false,
        'sort_order' => 44,
      ],
    ];
}
