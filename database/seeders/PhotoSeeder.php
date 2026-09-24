<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomActivity;
use App\Models\GalleryAlbum;
use Illuminate\Database\Seeder;

/**
 * The photo library the nursery sent us. The image files live in public/media (committed with the
 * code), this seeder recreates the database rows that place each photo on the right class, activity
 * or album. Generated from the local site with scratch/gen_photo_seeder.php after an import; to add
 * new photos, run `php artisan photos:import "<folder>"` locally and regenerate.
 *
 * Owners that already have photos are left alone, so the seeder is safe to re-run.
 */
class PhotoSeeder extends Seeder
{
    /** [owner type, class or album slug, activity slug, path, alt, width, height, sort] */
    private const PHOTOS = [
        [  'class_activity',  'candy',  'arabic',  'uploads/classroom_activity/22/Mqw7fpc5eyMYZop1WTmxK8G0.jpg',  'Arabic class in the Candy class at Marshmallow Nursery',  771,  1024,  0],
        [  'class_activity',  'candy',  'arabic',  'uploads/classroom_activity/22/JC1b3iSVAvP9cwz1mRxkdJVp.jpeg',  'Arabic class in the Candy class at Marshmallow Nursery',  768,  1024,  1],
        [  'class_activity',  'candy',  'topic-booklet',  'uploads/classroom_activity/20/ePdkIaGlawDkkvoXnA4f2umN.jpeg',  'Monthly topic booklet in the Candy class at Marshmallow Nursery',  1180,  1078,  0],
        [  'class_activity',  'candy',  'art-craft',  'uploads/classroom_activity/25/CkzfHLOw82skb8A3TiORKyv4.jpeg',  'Art & crafts in the Candy class at Marshmallow Nursery',  960,  1280,  0],
        [  'class_activity',  'candy',  'english-academic',  'uploads/classroom_activity/19/SRCgcZVf1NS89d5yT77fyZtM.jpg',  'English academic class in the Candy class at Marshmallow Nursery',  768,  1024,  0],
        [  'class_activity',  'candy',  'events',  'uploads/classroom_activity/29/7avACAZHJ0yPPhOLHKKTNNE0.jpg',  'Events & celebrations in the Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'candy',  'events',  'uploads/classroom_activity/29/V7a9m8qU2xHABVlA6OOhMjmR.jpg',  'Events & celebrations in the Candy class at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'candy',  'events',  'uploads/classroom_activity/29/QbGut0qwLoTdhZ4mwL4bhI3Q.jpg',  'Events & celebrations in the Candy class at Marshmallow Nursery',  640,  640,  2],
        [  'class_activity',  'candy',  'events',  'uploads/classroom_activity/29/4B4hUp411ANiY8kUOL2ebreK.jpg',  'Events & celebrations in the Candy class at Marshmallow Nursery',  640,  640,  3],
        [  'class_activity',  'candy',  'events',  'uploads/classroom_activity/29/1z2RZZz6Y3NH1zOiAYZZ33hk.jpg',  'Events & celebrations in the Candy class at Marshmallow Nursery',  640,  640,  4],
        [  'class_activity',  'candy',  'feelings',  'uploads/classroom_activity/26/gpCGs6VMKFaHfAS3wrCPQuAU.jpeg',  'Feelings & manners in the Candy class at Marshmallow Nursery',  727,  1024,  0],
        [  'class_activity',  'candy',  'feelings',  'uploads/classroom_activity/26/BCH751nQTZ950QCsYPxf5Fuz.jpeg',  'Feelings & manners in the Candy class at Marshmallow Nursery',  768,  1024,  1],
        [  'class_activity',  'candy',  'feelings',  'uploads/classroom_activity/26/xr5k7kR2InbcpycA8lA3958R.jpeg',  'Feelings & manners in the Candy class at Marshmallow Nursery',  768,  1024,  2],
        [  'class_activity',  'candy',  'motor-skills',  'uploads/classroom_activity/67/5J9w4wxuyNdDeoixVCzqO4Jn.jpg',  'Fine & gross motor skills in the Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'candy',  'motor-skills',  'uploads/classroom_activity/67/J9uPmdqoYScklcq6rmPAuSJQ.jpeg',  'Fine & gross motor skills in the Candy class at Marshmallow Nursery',  1280,  960,  1],
        [  'class_activity',  'candy',  'french',  'uploads/classroom_activity/21/EF7RBAp6F0bj79ZRcLnoBicv.jpg',  'French class in the Candy class at Marshmallow Nursery',  1024,  768,  0],
        [  'class_activity',  'candy',  'garden-time',  'uploads/classroom_activity/68/1r1m8M8ySdf8tZxq6dVicTeh.jpg',  'Garden time in the Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'candy',  'garden-time',  'uploads/classroom_activity/68/jO5m8dRQdaCmFoceQWbdhEbk.jpeg',  'Garden time in the Candy class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'candy',  'motor-skills',  'uploads/classroom_activity/67/WZg3Mvh4aAEaN9y9VoopqvOy.jpg',  'Fine & gross motor skills in the Candy class at Marshmallow Nursery',  640,  640,  2],
        [  'class_activity',  'candy',  'motor-skills',  'uploads/classroom_activity/67/aNi1dcC3YziBDPCU1E0MBXT2.jpeg',  'Fine & gross motor skills in the Candy class at Marshmallow Nursery',  960,  1280,  3],
        [  'class_activity',  'candy',  'gymnastics',  'uploads/classroom_activity/23/92e1ZkacR3QnymLFLXF0GTU8.jpg',  'Gymnastics in the Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'candy',  'gymnastics',  'uploads/classroom_activity/23/lTzrUbt1Xw3nCsOrajUog2es.jpg',  'Gymnastics in the Candy class at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'candy',  'science-experiments',  'uploads/classroom_activity/24/BJpAYx0l9e4GUsgQJpGDJM6q.jpg',  'Science experiments in the Candy class at Marshmallow Nursery',  768,  1024,  0],
        [  'class_activity',  'candy',  'science-experiments',  'uploads/classroom_activity/24/L3ZEvqaqIvWvRBSUanh6Nrp5.jpg',  'Science experiments in the Candy class at Marshmallow Nursery',  1024,  768,  1],
        [  'class_activity',  'candy',  'storytelling',  'uploads/classroom_activity/28/BKNhE4dH5IRBe4CzArHJqIWe.jpeg',  'Storytelling in the Candy class at Marshmallow Nursery',  1024,  768,  0],
        [  'class_activity',  'cotton-candy',  'arabic',  'uploads/classroom_activity/57/Z3TWilbWZ5nPSgaf3iJ2BmSy.jpg',  'Arabic class in the Cotton Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'cotton-candy',  'art-craft',  'uploads/classroom_activity/62/DKsV51IuQRZcH4v5UXDGuWOC.jpg',  'Art & crafts in the Cotton Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'cotton-candy',  'topic-booklet',  'uploads/classroom_activity/55/isDw0mWNu5cTTA820yOZeXX4.jpeg',  'Monthly topic booklet in the Cotton Candy class at Marshmallow Nursery',  1280,  960,  0],
        [  'class_activity',  'cotton-candy',  'topic-booklet',  'uploads/classroom_activity/55/pPOtqaZQ0RXn4CypuzgT1TZ1.jpeg',  'Monthly topic booklet in the Cotton Candy class at Marshmallow Nursery',  1186,  1078,  1],
        [  'class_activity',  'cotton-candy',  'cooking',  'uploads/classroom_activity/61/Lib5XJMzJgqENum8a67yR12g.jpeg',  'Cooking sessions in the Cotton Candy class at Marshmallow Nursery',  960,  1280,  0],
        [  'class_activity',  'cotton-candy',  'english-academic',  'uploads/classroom_activity/54/dcXo4VWFOTziiFB4mNR1r7PW.jpeg',  'English academic class in the Cotton Candy class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'cotton-candy',  'events',  'uploads/classroom_activity/65/ylp3r4FxVrZfYW8ObDzZ7rRx.jpg',  'Events & celebrations in the Cotton Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'cotton-candy',  'events',  'uploads/classroom_activity/65/tLINSicTqjulyyn8L8c45clb.jpeg',  'Events & celebrations in the Cotton Candy class at Marshmallow Nursery',  1600,  1200,  1],
        [  'class_activity',  'cotton-candy',  'motor-skills',  'uploads/classroom_activity/69/0HGRgzuCEqVvnE7HrjlbNlDy.jpeg',  'Fine & gross motor skills in the Cotton Candy class at Marshmallow Nursery',  1280,  960,  0],
        [  'class_activity',  'cotton-candy',  'french',  'uploads/classroom_activity/56/MKN6KM034gCUk5iMYIqXCMub.jpg',  'French class in the Cotton Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'cotton-candy',  'garden-time',  'uploads/classroom_activity/70/BI8NsM0584eG5A7MWdhJhrSr.jpeg',  'Garden time in the Cotton Candy class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'cotton-candy',  'motor-skills',  'uploads/classroom_activity/69/zbhRDYvhzddIuaUmDGsOe5hb.jpg',  'Fine & gross motor skills in the Cotton Candy class at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'cotton-candy',  'motor-skills',  'uploads/classroom_activity/69/jagMyF4dvFJ7vipj16XWZhrr.jpeg',  'Fine & gross motor skills in the Cotton Candy class at Marshmallow Nursery',  720,  1280,  2],
        [  'class_activity',  'cotton-candy',  'gymnastics',  'uploads/classroom_activity/58/0oxXBmmx2gvB6jrYvNszvSO1.jpeg',  'Gymnastics in the Cotton Candy class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'cotton-candy',  'music-dance',  'uploads/classroom_activity/71/ILCOHRUWqruYNJbb2xAupkhR.jpeg',  'Music & dancing in the Cotton Candy class at Marshmallow Nursery',  1280,  960,  0],
        [  'class_activity',  'cotton-candy',  'science-experiments',  'uploads/classroom_activity/60/o7IkDXCJysZpEsXkr6cnI8lN.jpg',  'Science experiments in the Cotton Candy class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'cotton-candy',  'storytelling',  'uploads/classroom_activity/63/s8xULleMYGkPBASEdz3M5szX.jpeg',  'Storytelling in the Cotton Candy class at Marshmallow Nursery',  960,  1280,  0],
        [  'class_activity',  'cotton-candy',  'monthly-trip',  'uploads/classroom_activity/64/Th0HMaCTG9UNG3MZrJHPZtOj.jpeg',  'Monthly trip in the Cotton Candy class at Marshmallow Nursery',  1280,  1009,  0],
        [  'class_activity',  'cotton-candy',  'monthly-trip',  'uploads/classroom_activity/64/SnsXQGx8nGyJvwF132bZUDDj.jpeg',  'Monthly trip in the Cotton Candy class at Marshmallow Nursery',  960,  1280,  1],
        [  'class',  'cupcake',  NULL,  'uploads/classroom/1/FGeJL5SJi3YRqYuWls0xqfyb.jpg',  'Cupcake class at Marshmallow Nursery',  1280,  960,  0],
        [  'class_activity',  'cupcake',  'topic-booklet',  'uploads/classroom_activity/72/L2enMHutllEage7BeY8mh8vX.jpeg',  'Monthly topic booklet in the Cupcake class at Marshmallow Nursery',  1172,  1078,  0],
        [  'class_activity',  'cupcake',  'events',  'uploads/classroom_activity/8/oYF6hJYOxmagBVCDB23fi7y6.jpeg',  'Events & celebrations in the Cupcake class at Marshmallow Nursery',  768,  1024,  0],
        [  'class_activity',  'cupcake',  'events',  'uploads/classroom_activity/8/7dm6172qO8KEpCeJKQqcVREk.jpeg',  'Events & celebrations in the Cupcake class at Marshmallow Nursery',  768,  1024,  1],
        [  'class_activity',  'cupcake',  'events',  'uploads/classroom_activity/8/34Ru9FXbrV1jQprHry7L6Ipq.jpeg',  'Events & celebrations in the Cupcake class at Marshmallow Nursery',  768,  1024,  2],
        [  'class_activity',  'cupcake',  'english-academic',  'uploads/classroom_activity/5/lBnSrnAMyJENmP1iSh0q6kby.jpg',  'English academic class in the Cupcake class at Marshmallow Nursery',  1004,  1024,  0],
        [  'class_activity',  'cupcake',  'events',  'uploads/classroom_activity/8/XlyX9EcaUI3rBdMgRAYVehfF.jpeg',  'Events & celebrations in the Cupcake class at Marshmallow Nursery',  960,  1280,  3],
        [  'class_activity',  'cupcake',  'events',  'uploads/classroom_activity/8/SIWE9bMAskO45tW5jHiYbGXC.jpeg',  'Events & celebrations in the Cupcake class at Marshmallow Nursery',  960,  1280,  4],
        [  'class_activity',  'cupcake',  'garden-time',  'uploads/classroom_activity/6/yxV2XCmTaHLKYEDYdhlZv4ek.jpeg',  'Garden time in the Cupcake class at Marshmallow Nursery',  768,  1024,  0],
        [  'class_activity',  'cupcake',  'garden-time',  'uploads/classroom_activity/6/ux2dGROIgFKuMfWM68x2z3iN.jpeg',  'Garden time in the Cupcake class at Marshmallow Nursery',  768,  1024,  1],
        [  'class_activity',  'cupcake',  'garden-time',  'uploads/classroom_activity/6/hKFfrPQFvRoSsI4QfKx8Bl97.jpeg',  'Garden time in the Cupcake class at Marshmallow Nursery',  768,  1024,  2],
        [  'class_activity',  'cupcake',  'motor-skills',  'uploads/classroom_activity/4/Xz2FBDy8ti30J7zq9x6pGDc3.jpeg',  'Fine & gross motor skills in the Cupcake class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'cupcake',  'motor-skills',  'uploads/classroom_activity/4/TUf1aaTLnlCfSszjjnGTnBuN.jpeg',  'Fine & gross motor skills in the Cupcake class at Marshmallow Nursery',  1200,  1600,  1],
        [  'class_activity',  'cupcake',  'music-dance',  'uploads/classroom_activity/2/1A0SL0Fd0kDXalu8WmQu5jGk.jpeg',  'Music & dancing in the Cupcake class at Marshmallow Nursery',  1024,  576,  0],
        [  'class_activity',  'cupcake',  'storytelling',  'uploads/classroom_activity/3/sL90oLzWzNUbqRbbWkfgM64n.jpg',  'Storytelling in the Cupcake class at Marshmallow Nursery',  768,  1024,  0],
        [  'album',  '6th-of-october-celebrations',  NULL,  'uploads/album/4/ufP0pEUvIyj48c4JI4f2a19L.jpg',  '6th of October celebrations at Marshmallow Nursery',  1080,  1080,  0],
        [  'album',  '6th-of-october-celebrations',  NULL,  'uploads/album/4/ETpx7DwIShjiWevo2FYi6YLV.jpg',  '6th of October celebrations at Marshmallow Nursery',  1080,  1080,  1],
        [  'album',  '6th-of-october-celebrations',  NULL,  'uploads/album/4/hNbdeqLGG1KKWfE9O4JIuom0.jpg',  '6th of October celebrations at Marshmallow Nursery',  1080,  1080,  2],
        [  'album',  '6th-of-october-celebrations',  NULL,  'uploads/album/4/OPRcYTfb4Z6CdPEziID6ZNrD.jpg',  '6th of October celebrations at Marshmallow Nursery',  1080,  1080,  3],
        [  'album',  '6th-of-october-celebrations',  NULL,  'uploads/album/4/el0ax9bnx0w1okubDBcmlv3O.jpg',  '6th of October celebrations at Marshmallow Nursery',  1080,  1080,  4],
        [  'album',  '6th-of-october-celebrations',  NULL,  'uploads/album/4/mAc57CXfOZkp2crtjIatM5Lg.jpg',  '6th of October celebrations at Marshmallow Nursery',  1080,  1080,  5],
        [  'album',  'animals-visit',  NULL,  'uploads/album/9/Avo0EFJpq7bnog2MQECwTmPD.jpg',  'Animals visit at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'animals-visit',  NULL,  'uploads/album/9/odyXeo3MV9i1bjXUFoUBrh8W.jpg',  'Animals visit at Marshmallow Nursery',  640,  640,  1],
        [  'album',  'animals-visit',  NULL,  'uploads/album/9/Yi0kOm2zFPYnQa4QjWBcbXw1.jpg',  'Animals visit at Marshmallow Nursery',  640,  640,  2],
        [  'album',  'animals-visit',  NULL,  'uploads/album/9/87GrDXk3jNMF3FyPpSryYV81.jpg',  'Animals visit at Marshmallow Nursery',  576,  576,  3],
        [  'album',  'animals-visit',  NULL,  'uploads/album/9/NidzcZWjWdRDJGRtbD3c9Q9M.jpg',  'Animals visit at Marshmallow Nursery',  640,  640,  4],
        [  'album',  'animals-visit',  NULL,  'uploads/album/9/yF5Hgqgx7P12vgTWO7Kf2oE8.jpg',  'Animals visit at Marshmallow Nursery',  640,  640,  5],
        [  'album',  'circus-day',  NULL,  'uploads/album/10/EJLYgYYMJG5o2QDE6hFioTFL.jpg',  'Circus day at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'circus-day',  NULL,  'uploads/album/10/FhOLVZ7IUITOj7ifA1jUY5ts.jpg',  'Circus day at Marshmallow Nursery',  640,  640,  1],
        [  'album',  'circus-day',  NULL,  'uploads/album/10/fS0L5c1AlgDOWjRoZDh1byFT.jpg',  'Circus day at Marshmallow Nursery',  640,  640,  2],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/keh4bHVfKY9iF6Gblb04QVme.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/QbdEw2hB6t1KfXmYDlrJitql.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  640,  640,  1],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/dhrFqgtuG0PnP98v8XKuVe7B.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  1080,  607,  2],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/ZRRg4wBsNSst5sHFgCJJlhZR.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  502,  502,  3],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/6dcDMfTFZfwe1VXekxK3RvLC.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  366,  366,  4],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/2xLlNyBmoV1WbuNBnOeMp6WN.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  496,  496,  5],
        [  'album',  'graduation-2026-under-the-sea',  NULL,  'uploads/album/1/muK8JFQJHgcIK8kX84qYTy2m.jpg',  'Graduation 2026: Under the Sea at Marshmallow Nursery',  488,  488,  6],
        [  'album',  'marshmallow-schools-expo',  NULL,  'uploads/album/11/oYrIbhhrlbHzvAm0k3Wm4UBs.jpg',  'Marshmallow Schools Expo at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'marshmallow-schools-expo',  NULL,  'uploads/album/11/9kaOeeOqHHyOXAgo7QZgCTLl.jpg',  'Marshmallow Schools Expo at Marshmallow Nursery',  640,  640,  1],
        [  'album',  'marshmallow-schools-expo',  NULL,  'uploads/album/11/U149YNEghk8pgTHKoeXaZM4o.jpg',  'Marshmallow Schools Expo at Marshmallow Nursery',  640,  640,  2],
        [  'album',  'solar-system-competition',  NULL,  'uploads/album/2/CEVvvmNcFtzTgRZQGJXZ3y8Z.jpg',  'Solar System Competition at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'solar-system-competition',  NULL,  'uploads/album/2/ZLDVrUmf5VQAmAfz4KKEXNri.jpg',  'Solar System Competition at Marshmallow Nursery',  640,  640,  1],
        [  'album',  'summer-camp-2026',  NULL,  'uploads/album/7/u9IvacIwO82rvA1au5BVkio7.jpg',  'Summer Camp 2026 at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'summer-camp-2026',  NULL,  'uploads/album/7/khlwOgV86O5NW900crxtavF1.jpg',  'Summer Camp 2026 at Marshmallow Nursery',  640,  640,  1],
        [  'album',  'summer-camp-2026',  NULL,  'uploads/album/7/7tiMyrr2wiWnVvjlazbp9JYa.jpg',  'Summer Camp 2026 at Marshmallow Nursery',  1200,  1600,  2],
        [  'album',  'summer-camp-2026',  NULL,  'uploads/album/7/wggO9fyiSgLtTIuEUdbfBaEY.jpg',  'Summer Camp 2026 at Marshmallow Nursery',  1200,  1600,  3],
        [  'album',  'trip-to-dolphina',  NULL,  'uploads/album/6/h6Gua4iFZxdGmmZmvmkTkvRP.jpg',  'Trip to Dolphina at Marshmallow Nursery',  640,  640,  0],
        [  'album',  'trip-to-dolphina',  NULL,  'uploads/album/6/akmy5U82AiNRciH7WP7rRrm0.jpg',  'Trip to Dolphina at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'ice-cream',  'arabic',  'uploads/classroom_activity/33/9n9j1Tpvjn44IAE0GUEbPKws.jpg',  'Arabic class in the Ice Cream class at Marshmallow Nursery',  576,  1024,  0],
        [  'class_activity',  'ice-cream',  'art-craft',  'uploads/classroom_activity/38/bkpCpfh8cSsn7BZKdUIQWCaf.jpg',  'Art & crafts in the Ice Cream class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'ice-cream',  'topic-booklet',  'uploads/classroom_activity/31/ecRx5lcEZpyicMXojIQE9qCF.jpeg',  'Monthly topic booklet in the Ice Cream class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'ice-cream',  'topic-booklet',  'uploads/classroom_activity/31/B4PyU9VabEKjinMrlrNO1KWO.jpeg',  'Monthly topic booklet in the Ice Cream class at Marshmallow Nursery',  1186,  1078,  1],
        [  'class_activity',  'ice-cream',  'cooking',  'uploads/classroom_activity/37/t4iJrOGN9HCo0e9l5SbCbePE.jpg',  'Cooking sessions in the Ice Cream class at Marshmallow Nursery',  1024,  768,  0],
        [  'class_activity',  'ice-cream',  'english-academic',  'uploads/classroom_activity/30/UsHbE08yqTGaeSMgmKJ8sIDU.jpg',  'English academic class in the Ice Cream class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'ice-cream',  'english-academic',  'uploads/classroom_activity/30/b3gqbhBhru8EzMObu2nMv7I2.jpeg',  'English academic class in the Ice Cream class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'ice-cream',  'events',  'uploads/classroom_activity/41/7sSCi4vhakgVRvFy5gbigFsb.jpg',  'Events & celebrations in the Ice Cream class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'ice-cream',  'events',  'uploads/classroom_activity/41/UEay5rEwucFlmuZKpLyMEh57.jpg',  'Events & celebrations in the Ice Cream class at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'ice-cream',  'events',  'uploads/classroom_activity/41/rmZ1b510J9ja3qqDZ71nVq7N.jpg',  'Events & celebrations in the Ice Cream class at Marshmallow Nursery',  640,  640,  2],
        [  'class_activity',  'ice-cream',  'motor-skills',  'uploads/classroom_activity/73/sJvBTOvS0g5yjzdnaCpyAxCD.jpeg',  'Fine & gross motor skills in the Ice Cream class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'ice-cream',  'french',  'uploads/classroom_activity/32/dW4bZ3P7qApNEoBrs8A2w07r.jpeg',  'French class in the Ice Cream class at Marshmallow Nursery',  1024,  768,  0],
        [  'class_activity',  'ice-cream',  'garden-time',  'uploads/classroom_activity/74/KCYHnnoZw215E5c2L132VK2w.jpeg',  'Garden time in the Ice Cream class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'ice-cream',  'garden-time',  'uploads/classroom_activity/74/2DfPJABJB2qIfUh2p8XxXuSg.jpeg',  'Garden time in the Ice Cream class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'ice-cream',  'motor-skills',  'uploads/classroom_activity/73/qLbaiWSBAak0eloRaYwslAjw.jpeg',  'Fine & gross motor skills in the Ice Cream class at Marshmallow Nursery',  720,  1280,  1],
        [  'class_activity',  'ice-cream',  'gymnastics',  'uploads/classroom_activity/34/KdGJJOb58UWQf8eUaDBxdYvF.jpg',  'Gymnastics in the Ice Cream class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'ice-cream',  'music-dance',  'uploads/classroom_activity/39/7EIElPJZmvtYPcxnZNtG2vv9.jpg',  'Music & dancing in the Ice Cream class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'ice-cream',  'science-experiments',  'uploads/classroom_activity/36/3myQ5il90BHh6ou14hxFE5rj.jpg',  'Science experiments in the Ice Cream class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'ice-cream',  'monthly-trip',  'uploads/classroom_activity/40/8FcteNIRNjYQu4FMaIW9uCX5.jpg',  'Monthly trip in the Ice Cream class at Marshmallow Nursery',  1024,  576,  0],
        [  'class_activity',  'ice-cream',  'monthly-trip',  'uploads/classroom_activity/40/ND8LDf9UgILBMc00KAw69OmI.jpeg',  'Monthly trip in the Ice Cream class at Marshmallow Nursery',  1024,  768,  1],
        [  'class_activity',  'lollipop',  'arabic',  'uploads/classroom_activity/45/bJ12l8JKmedy1uUBsTQisAY6.jpg',  'Arabic class in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'arabic',  'uploads/classroom_activity/45/n1cN0XsFzqq1QSjWRJTZz2Ok.jpg',  'Arabic class in the Lollipop class at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'lollipop',  'art-craft',  'uploads/classroom_activity/50/rteoPK6cIhuDT6YKRikC3oIj.jpg',  'Art & crafts in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'topic-booklet',  'uploads/classroom_activity/43/KknjVvgbQbGeIS7bAzoqDgDz.jpeg',  'Monthly topic booklet in the Lollipop class at Marshmallow Nursery',  1186,  1078,  0],
        [  'class_activity',  'lollipop',  'cooking',  'uploads/classroom_activity/49/vn5W7i0V8GbzNuJZsTLRDzCE.jpeg',  'Cooking sessions in the Lollipop class at Marshmallow Nursery',  960,  1280,  0],
        [  'class_activity',  'lollipop',  'cooking',  'uploads/classroom_activity/49/45HzFFmLhtx1IoV5NmgV9Lgt.jpeg',  'Cooking sessions in the Lollipop class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'lollipop',  'english-academic',  'uploads/classroom_activity/42/ZVntXOzsSM4oTJr2gaLABFFh.jpeg',  'English academic class in the Lollipop class at Marshmallow Nursery',  1600,  1200,  0],
        [  'class_activity',  'lollipop',  'english-academic',  'uploads/classroom_activity/42/hCDvNl9UUM15CtgoRpamcRrh.jpeg',  'English academic class in the Lollipop class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'lollipop',  'events',  'uploads/classroom_activity/53/Iyzl4X4kt1Bvc1h5Hdc65DAI.jpg',  'Events & celebrations in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'events',  'uploads/classroom_activity/53/WnoJQRfwD3LYZ9qZYbFpom8S.jpg',  'Events & celebrations in the Lollipop class at Marshmallow Nursery',  640,  640,  1],
        [  'class_activity',  'lollipop',  'events',  'uploads/classroom_activity/53/MVhWVoDn4gfvQewk8gcJ6aao.jpg',  'Events & celebrations in the Lollipop class at Marshmallow Nursery',  640,  640,  2],
        [  'class_activity',  'lollipop',  'motor-skills',  'uploads/classroom_activity/75/9QcswIt4yTtPeYCPsSeD7SpR.jpeg',  'Fine & gross motor skills in the Lollipop class at Marshmallow Nursery',  1330,  1600,  0],
        [  'class_activity',  'lollipop',  'french',  'uploads/classroom_activity/44/BmEnw8IaGnz6Dd9SxRaQmeq2.jpg',  'French class in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'garden-time',  'uploads/classroom_activity/76/V03qhxSyteEDpEfu17cjbXY9.jpeg',  'Garden time in the Lollipop class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'lollipop',  'motor-skills',  'uploads/classroom_activity/75/4ZAVRfsRNLxFwpJZQ7P4rTms.jpeg',  'Fine & gross motor skills in the Lollipop class at Marshmallow Nursery',  720,  1280,  1],
        [  'class_activity',  'lollipop',  'gymnastics',  'uploads/classroom_activity/46/88sFBQFiFgnz4qQm5GJcFol2.jpeg',  'Gymnastics in the Lollipop class at Marshmallow Nursery',  1200,  1600,  0],
        [  'class_activity',  'lollipop',  'music-dance',  'uploads/classroom_activity/77/f5ahEUk9dXjkyH6jNxvZwLAQ.jpg',  'Music & dancing in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'science-experiments',  'uploads/classroom_activity/48/AKyQBEObx8jHXzRMbie3ZtL8.jpg',  'Science experiments in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'storytelling',  'uploads/classroom_activity/78/BGVjXeGZcuKcQ05CaAirxLIi.jpeg',  'Storytelling in the Lollipop class at Marshmallow Nursery',  960,  1280,  0],
        [  'class_activity',  'lollipop',  'monthly-trip',  'uploads/classroom_activity/52/nau526gDCapMX9WK5PyYTH71.jpg',  'Monthly trip in the Lollipop class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'lollipop',  'monthly-trip',  'uploads/classroom_activity/52/E09sHLETBSRnvryPT8vyabPC.jpeg',  'Monthly trip in the Lollipop class at Marshmallow Nursery',  960,  1280,  1],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/DrnFA6YZ2JpPbkJq4guOHSqj.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  0],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/6PZUA8UFXjK7sbtpdmKX3zwv.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  1],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/e9ndBcNad8B8YuIyH3C53qao.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  2],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/WAf0wk46xPo3Myg0kGVuDhsb.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  3],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/qNlxDxUozZYK9uoCyy6q4euf.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  4],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/34wvvJ3DOcga9L9lgyrzkZHx.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  5],
        [  'album',  'parents-reviews',  NULL,  'uploads/album/12/JRzCvtSckqXUoTkD3xxpioOY.jpg',  'Parents reviews at Marshmallow Nursery',  1080,  1350,  6],
        [  'class_activity',  'popcorn',  'topic-booklet',  'uploads/classroom_activity/79/RLPTQll6X8znpTZcwqGZ5UOb.jpeg',  'Monthly topic booklet in the Popcorn class at Marshmallow Nursery',  1180,  1078,  0],
        [  'class_activity',  'popcorn',  'art-craft',  'uploads/classroom_activity/14/NQ1N3mN4zFepaFjaNpZCOKg7.jpg',  'Art & crafts in the Popcorn class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'popcorn',  'art-craft',  'uploads/classroom_activity/14/MQ8dWfyCxbndsxwUesZqHFBa.jpeg',  'Art & crafts in the Popcorn class at Marshmallow Nursery',  1200,  1600,  1],
        [  'class_activity',  'popcorn',  'events',  'uploads/classroom_activity/18/DPOxbnsGAOryv4bJ332Ki3M0.jpg',  'Events & celebrations in the Popcorn class at Marshmallow Nursery',  640,  640,  0],
        [  'class_activity',  'popcorn',  'events',  'uploads/classroom_activity/18/BLYLJmNK8oTL3NMvmEc7kbrk.jpeg',  'Events & celebrations in the Popcorn class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'popcorn',  'motor-skills',  'uploads/classroom_activity/10/jf9sdi4LFG0KY4yNnuoSEe6C.jpeg',  'Fine & gross motor skills in the Popcorn class at Marshmallow Nursery',  1280,  720,  0],
        [  'class_activity',  'popcorn',  'motor-skills',  'uploads/classroom_activity/10/4zLpyFAJ8hEWY8H9kJjXsFWC.jpeg',  'Fine & gross motor skills in the Popcorn class at Marshmallow Nursery',  960,  1280,  1],
        [  'class_activity',  'popcorn',  'motor-skills',  'uploads/classroom_activity/10/KJOqB3yNb5pRzaWOiBIC1nDQ.jpeg',  'Fine & gross motor skills in the Popcorn class at Marshmallow Nursery',  960,  1280,  2],
        [  'class_activity',  'popcorn',  'gymnastics',  'uploads/classroom_activity/11/XXQH1gO9Y3z50yEGqT6vLFU1.jpg',  'Gymnastics in the Popcorn class at Marshmallow Nursery',  577,  1024,  0],
        [  'class_activity',  'popcorn',  'gymnastics',  'uploads/classroom_activity/11/LalIhWpRLq0VmZFWl82pmt4c.jpeg',  'Gymnastics in the Popcorn class at Marshmallow Nursery',  768,  1024,  1],
        [  'class_activity',  'popcorn',  'storytelling',  'uploads/classroom_activity/15/u4XQldfejZlvuEmGlYSpj33k.jpeg',  'Storytelling in the Popcorn class at Marshmallow Nursery',  1024,  768,  0],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/orHFigT6oXDlWNK8IAfKGHqr.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1640,  624,  1],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/AxN6C8RcmCrk87gKfx3zDgL3.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  5],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/uJGIyw44kjiH6fKBaktKHtPO.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  9],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/YKBIbKR77EPEaIaQ64qdaL5M.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  13],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/ybsR9w3WGmr4LLlyzmQ748bO.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  17],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/CCASFIVlOW8cNTqxpDyHao41.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1281,  22],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/pQcNDppEcK9atksAjkceJuUy.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  26],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/bgwygMTU0inFftgX6oDLdgnp.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  30],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/lMYHin3sh0ysKpCKF2pcp2AC.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  34],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/RyxJWrdu64kenXj2Hi1OMM7J.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  39],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/AKDnMcTED7WsAJBxK6Gp0lGf.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  43],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/42elBPwY4Jz0ZJF9ji3zjWqy.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  47],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/SXvmo2KyWlzxo2X9Ka3tuaD8.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  51],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/wXgJQMnbz56KUkha7KLzIv29.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  56],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/j5Xhh59HtSYkL7Nz1z3dTEqn.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  60],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/7z1i25qr1mlgoFt99eHpDy6R.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  64],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/zoeaMN587HGnEbHp2RmMlvb9.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  68],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/uMlmbnEbnIPFlo6raUveqr2e.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  72],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/R2uD1bj3vGurt2JbllQXhqO6.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  77],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/qQbMbOJOcggJOZxPVTxJKuKk.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  81],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/4Pz8PAFgUMynp8idje4GSXW3.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  85],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/kynMsv8uQjvIWpL8s57p6zVZ.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1280,  1920,  89],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/xlfoCOfswPSbiIRJSHOk5IzF.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1271,  94],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/B8Kn7YCChf9WUoBIP0b95wxE.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  98],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/HEAlJOqTbddpuLhkm2dcZx3F.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  102],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/AnjyxCyFxM6RZEfupZYMDPtT.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  106],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/C4OWnJHgRDKOXY3ZLet8zMAu.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1280,  1920,  111],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/r3iiIpwrHOUOjWgWWp71PRMs.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  115],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/NMzHJtN0cnoTnq6LA6bwizkV.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  119],
        [  'album',  'pyjama-camping-party-bedtime-stories',  NULL,  'uploads/album/3/JbJ5Hseco83kH41Ll0q1SrQE.jpg',  'Pyjama Camping Party: Bedtime Stories at Marshmallow Nursery',  1920,  1280,  123],
    ];

    private const COVERS = [
        ['class', 'cupcake', 'uploads/classroom/1/FGeJL5SJi3YRqYuWls0xqfyb.jpg'],
        ['class', 'popcorn', 'uploads/classroom_activity/79/RLPTQll6X8znpTZcwqGZ5UOb.jpeg'],
        ['class', 'candy', 'uploads/classroom_activity/22/Mqw7fpc5eyMYZop1WTmxK8G0.jpg'],
        ['class', 'ice-cream', 'uploads/classroom_activity/33/9n9j1Tpvjn44IAE0GUEbPKws.jpg'],
        ['class', 'lollipop', 'uploads/classroom_activity/45/bJ12l8JKmedy1uUBsTQisAY6.jpg'],
        ['class', 'cotton-candy', 'uploads/classroom_activity/57/Z3TWilbWZ5nPSgaf3iJ2BmSy.jpg'],
        ['album', 'graduation-2026-under-the-sea', 'uploads/album/1/keh4bHVfKY9iF6Gblb04QVme.jpg'],
        ['album', 'solar-system-competition', 'uploads/album/2/CEVvvmNcFtzTgRZQGJXZ3y8Z.jpg'],
        ['album', 'pyjama-camping-party-bedtime-stories', 'uploads/album/3/orHFigT6oXDlWNK8IAfKGHqr.jpg'],
        ['album', '6th-of-october-celebrations', 'uploads/album/4/ufP0pEUvIyj48c4JI4f2a19L.jpg'],
        ['album', 'trip-to-dolphina', 'uploads/album/6/h6Gua4iFZxdGmmZmvmkTkvRP.jpg'],
        ['album', 'summer-camp-2026', 'uploads/album/7/u9IvacIwO82rvA1au5BVkio7.jpg'],
        ['album', 'animals-visit', 'uploads/album/9/Avo0EFJpq7bnog2MQECwTmPD.jpg'],
        ['album', 'circus-day', 'uploads/album/10/EJLYgYYMJG5o2QDE6hFioTFL.jpg'],
        ['album', 'marshmallow-schools-expo', 'uploads/album/11/oYrIbhhrlbHzvAm0k3Wm4UBs.jpg'],
        ['album', 'parents-reviews', 'uploads/album/12/DrnFA6YZ2JpPbkJq4guOHSqj.jpg'],
    ];

    public function run(): void
    {
        $classrooms = Classroom::pluck('id', 'slug');
        $albums = GalleryAlbum::pluck('id', 'slug');
        $pivots = ClassroomActivity::with(['classroom:id,slug', 'activity:id,slug'])->get()
            ->mapWithKeys(fn ($p) => [$p->classroom->slug.'/'.$p->activity->slug => $p]);
        $seen = [];

        foreach (self::PHOTOS as [$kind, $slug, $activity, $path, $alt, $width, $height, $sort]) {
            $owner = match ($kind) {
                'class' => isset($classrooms[$slug]) ? Classroom::find($classrooms[$slug]) : null,
                'album' => isset($albums[$slug]) ? GalleryAlbum::find($albums[$slug]) : null,
                'class_activity' => $pivots[$slug.'/'.$activity] ?? null,
                default => null,
            };

            if (! $owner) {
                continue;
            }

            $key = $owner->getMorphClass().':'.$owner->getKey();
            if (! isset($seen[$key])) {
                $seen[$key] = $owner->photos()->exists();
            }
            if ($seen[$key]) {
                continue;
            }

            $owner->photos()->create(compact('path', 'alt', 'width', 'height') + ['sort_order' => $sort]);
        }

        foreach (self::COVERS as [$kind, $slug, $path]) {
            $model = $kind === 'class'
                ? Classroom::where('slug', $slug)->first()
                : GalleryAlbum::where('slug', $slug)->first();

            if ($model && ! $model->cover_image) {
                $model->update(['cover_image' => $path]);
            }
        }

        GalleryAlbum::doesntHave('photos')->update(['is_visible' => false]);
        GalleryAlbum::has('photos')->update(['is_visible' => true]);
    }
}
