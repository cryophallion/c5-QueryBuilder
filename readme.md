# Concrete5 Query Builder

## Description
Simple library that will construct database queries for you. Includes functions for getting attribute information with the query.

##Why on earth would anyone need another query builder?
While working on a project, I had a lot of list queries that also needed attribute information.
It seemed a complete waste of processing power and an obscene number of extra queries to get attribute information for every user in a certain group when I know a good old join would do the trick. Thus became this library.

##How do I use it?
Place it in your root library folder, or package library folder and reference it (I use it in packages usually). I've included 3 real life demo subclasses to show how it works. Additionally, images shows a subquery trick for getting fileversion info.

###Final note:
I'd love any input on this, good and bad. The best way to make software better is to get outside opinions, and I'd like to hear yours!