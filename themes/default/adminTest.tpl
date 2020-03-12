<h2>
	admin test template
</h2><p>
[?ifRank:toto]
	<p>TOTO</p>
	<p>Il est des nôôôôtreuh ! Il sait pas coder commeuh les ôôtreuh !</p>
[?fi:toto]
[?ifRank:COMMENTAIRE]
	//////////////////////////////////////////////
	TODO : Make a real link for admin link if it is needed
	//////////////////////////////////////////////
[?fi:COMMENTAIRE]
[?ifRank:admin]
	<a href="../admin/">Lien vers l'administration</a><br />
	<?php return 'TEST IF ADMIN<hr />'; ?>
	[#tests/test1]
[?fi:admin]
Ceci est le test du if pour le rang administration</p>
