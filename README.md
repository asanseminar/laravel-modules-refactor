
## laravel-modules Refactor Command

This laravel command is a sample command that you can change and adapt to your
own project structure to refactor a Laravel project to the structure of 
laravel-moduels library folder structure.

**Note:** We have used a modified version of the current code for moving our production code
at [AsanSeminar](https://asanseminar.ir) to use laravel-modules. (It's not replaced in production yet)

## Requirements

- An awesome Laravel Project
- Git
- [laravel-modules libraray](https://github.com/nWidart/laravel-modules)
- [phpactor](https://github.com/phpactor/phpactor) to help moving Classes


## Usage

- Install [laravel-modules libraray](https://github.com/nWidart/laravel-modules) library
- Install [phpactor](https://github.com/phpactor/phpactor) command line tool


- Create new branch in your codebase for Refactoring
- Copy `Refactor` folder into your `app/Console/Commands` folder
- Copy `clean_move.sh` to your laravel base folder
- Add and Commit the `clean_move.sh` file to your git so it wouldn't get removed when you run it


- Take a look at Refactoring commands to get a gist of what they are doing
- You can run code to see what happens. (Don't worry you can run `clean_move.sh` to return back changes whenever you want)
- Update `Refactor/mappings.json`, Create/Update/Delete Mover classes, run the command so eventually get your desired results
- You can enable or disable Movers in `ModulesMoveClasses`


**CAVEAT:**
**If your code is on production, note that moving your `User` model may invalidate all of your sessions! After struggling with different ideas we decided on not playing with it!**


**Known Issues:**
- Please note that this is written in very limited time and is OpenSourced to give others a boilerplate to do the job, so it's not a well organized code
- We have changed the code to not expose our project details and the current code is not tested
