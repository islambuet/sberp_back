###New project
laravel new sberp_back

##How to run from git

composer install
#php artisan key:generate

php artisan migrate:fresh --seed #for database with seed
php artisan serve

####Migration Command
#php artisan make:migration create_user_groups
#php artisan make:seeder UserGroupsSeeder
#php artisan make:mail MailSender
#php artisan db:seed --class=SystemTasksSeeder