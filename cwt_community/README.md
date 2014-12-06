Typo3 Extension "CWT Community"
===============================

What does it do?
-----------------
This extension provides a wide range of community features for frontend users of a Typo3 website. All of them are implemented as Typo3 plugins so that they can easily be inserted on any Typo3 website. The following sections contain a more detailed description of this extension's features. However, you do not have to use all of them on your website. Please read the installation section for more information on which features you can combine and which not. The layout is completely customizable by changing the provided HTML templates. Additionally most of the icons used within this extension can be changed via the Constant Editor.

Frontend
--------
* Userlist – This view provides an alphabetic list of all community users (fe_users). From here you can add other users to your buddylist and you are able to send messages to community participants.
It is also possible to create other userlists like “the 10 newest community users”.
* Profile – Every user has his own personal profile page, where his realname, city, e-mail, online status, website... and a photo are displayed.
It is also possible to create more than one profile page.
* Profile Mini – Every user has their own personal mini profile page, where only their name and small foto is displayed.
* Profile Administration – The community users are able to administer their profile pages by themselves. (using CWT Frontend Edit)
* Guestbook – Every user has his own guest book, where other users can pin their messages. The guest book owning user can close and open his guest book for postings. Furthermore the user can delete entries from his guest book.
* Messages – All community users can send and receive messages from other community members. Therefore this extension provides a personal message box for every single user, where he is able to delete and reply on messages. 
* Buddylist – Every user can keep his own address book with this feature. The user can send messages from this view directly and is able to see the online status of his buddies.
* Buddyadmin – Every user can manage it's buddy request from this page. They can see their outgoing and incoming buddy requests.
* Welcome – This is intended to be the personal start page for users, which may be shown after login. The user will be informed about new community mail, which might have arrived since his last login. In addition to that new buddy invitations are displayed.
* Gallery – This features a personal photo gallery for each frontend user, where he will be able to create his own photo albums and put pictures in it. The authorization system allows to defines access rights (all community members, only those on his buddylist or nobody) on a per album basis. Finally T3 frontend users are able to put comments on each photo.
* Search – The community extension provides two different search masks which can be used to find frontend users based on a versatile set of search options: username, age, city, etc..
* Abuse report – The community users are able to report abuse i.e. for a user's gallery or profile.
Userstats – Community users are able to see statistics i.e. how often their profile has been visited.

Backend
-------
* User Administration – You can enable and disable community users in the backend.
* Gallery Administration – The administration of the frontend user galleries is tightly integrated in the backend userlist. The administrator is able to delete complete albums, single photo or even single comments for a photo.
* Mailing – The mailing module can send messages to all frontend users or to members of a specific usergroup. The messages will then appear in the appropriate users' inboxes'.
* Abuse report Administration– Administrators are able to see abuse reports from users and react on them.

Where can I get the binaries?
----------------------------------
* Download: http://typo3.org/extensions/repository/view/cwt_community
* More Information: http://www.faulhaber.it/typo3-extensions/cwt-community/

ToDo List
---------
[] Upgrade to newer jQuery Version
[] Add documentation for wall functionality
