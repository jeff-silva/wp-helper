# Installation
1) In your theme folder, create a sub folder called "hp-helpers";
2) Put file "wp-helpers.php" inside it;
3) In your "functions.php" file, insert the line above in some place:
```
include __DIR__ . '/wp-helpers/hp-helpers.php';
```
4) Download the files necessary to your project.

The "wp-helpers.php" file has a auto-include line, dont put it in your theme base folder or it will include "functions.php" and other files again and broke your site.
