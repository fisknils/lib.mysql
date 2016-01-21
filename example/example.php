<?PHP
require_once("../class.mysql.php");


$mysql_username = "mycompany";
$mysql_password = "mysupersecretpassword";
$mysql_database = "mycompany";
$mysql_hostname = "localhost";
$mysql_port     = 3306;

$db = new mysql_conn($mysql_username, $mysql_password, $mysql_database, $mysql_hostname, $mysql_port);



$sites = [
    "myfirstsite.com" => getArticlesByDomain("myfirstsite.com"),
    "mysecondsite.com" => getArticlesByDomain("mysecondsite.com"),
    "mythirdsite.com" => getArticlesByDomain("mythirdsite.com")
];


if($argv) {
    print_r($sites);
} else {
    echo "<pre>" . print_r($sites,1) . "</pre>";
}







function getArticlesByDomain($domain) {
    return getArticlesBySiteID(getSiteIDByDomain($domain));
}

// returns site_ids by domain name.
function getSiteIDByDomain($domain) {
    global $db;
    return $db->query("select id from sites where domain = '",$domain,"'")[0]['id'];
}    

/**
 * returns articles marked for a site by it's site_id
 * not pretty, but it does the job
 **/
function getArticlesBySiteID($site_id,$from_limit=0,$to_limit=10,$order="desc") {
    global $db;
    return $db->query("select article,id,title from articles inner join article_relations on article_relations.article_id=articles.id where article_relations.site_id = '",$site_id,"' order by articles.id $order limit $from_limit , $to_limit");
}
