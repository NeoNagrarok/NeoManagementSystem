<h2>
	admin test template
</h2><p>
[?ifRank:toto]
	<p>TOTO</p>
	<p>Il est des nôôôôtreuh ! Il sait pas coder commeuh les ôôtreuh !</p>
	[?ifRank:admin]
		<a href="admin/">Lien vers l'administration</a><br />
		<?php return 'TEST IF ADMIN<hr />'; ?>
		[#tests/test1]
	[?fi:toto]
[?fi:admin]
Ceci est le test du if pour le rang administration</p>
