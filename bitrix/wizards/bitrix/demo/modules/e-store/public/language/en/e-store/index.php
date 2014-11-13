<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("e-Store");
?>
<p>The <b>e-Store </b>module helps you set up a web store where you can sell products, services and
media.</p>
<p>The demo site shows an example of selling books. The <b>e-Store </b>(/e-store/) section contains
two subsections: <b>Books </b>(<b>/e-store/books/</b>) and <b>Affiliates </b>(<b>/e-store/affiliates/</b>).</p>
<p>To provide a better user experience, the catalog is structured by creating a new information
block type (<b>Book catalog</b>), for which the following information blocks were created:</p>
<ul>
	<li>Authors;
	<li>Books;
	<li>Reviews.</li>
</ul>
<p>Here, the <b>Books </b>information block is a commercial catalog whose elements are displayed for
sale in the <b>e-Store</b>. The catalog is rendered using the <b>Full catalog </b>component. The <b>Books
</b>section offers two subsections: Authors and Reviews, where the first one contains information
about authors, while the latter displays reviews about books.</p>
<p>The structure is built in such a way that each book is linked to its author. To achieve this, a
special property <b>Authors</b> (of the <i>Link to element </i>type) was added to the <b>Books</b>
information block. Additionally, each <b>Review </b>is linked to a certain book by creating a <b>Book
review </b>property (of the <i>Link to element </i>type).</p>
<p>An <i>affiliate </i>is a special kind of a commercial partner in the Internet that does not
directly sell products but publishes a link to a merchant's product page on a web site. If a
customer was driven by that link and purchased a product, the affiliate gains a commission indicated
in the contract. The <b>Affiliates</b> section spotlights on the following features illustrating the
concept:</p>
<ul>
	<li>the <b>Affiliate account</b> page displays information on the affiliates' account money
		flow;
	<li>example of the affiliate registration form;</li>
	<li>example of displaying the existing affiliate plans using visual components;</li>
	<li>bonus: instructions on creating the product purchase and affiliate registration links.</li>
</ul>
<p>The <b>e-Store</b> module is also capable of selling the on-line content. In the context of
Bitrix Site Manager, the content selling is adding a user to a user group having enough permissions
to access the information to be sold. As an example, a user can be added to the <b>Subscribers </b>user
group for one month: this group is given the permission to view full articles.</p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>