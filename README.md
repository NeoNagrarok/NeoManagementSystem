# NeoManagementSystem

## Notes

Rework folders architecture : at least put all .tpl files into template folder in order to be more clear, more easy to recognize good thing

Also make a config folder and put in one file all defines contained into index.php and in one other file all includes (i think there right place is here). Plus move routes.json in.

## TODO

- Do an input into logs/connect.log (which may be renamed) or eventually in other fil in logs/ for each admin command used (install / uninstall cms, create new contentModel, configure contentModel, add content, install / uninstall module, configure module etc.)
- Make a real error handling
- Make user handling
- Make internationalization
- Make navigation handling
- Securize access at any .php .js or other file which is a code or configuration file
- Make a .gitignore (at least for the .mwb.bak)
- Include webpack (at least for sass, may be also for react ?)
- Make an API for externals process
