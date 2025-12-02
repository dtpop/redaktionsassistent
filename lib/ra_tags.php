<?php
// Der Code wurde hauptsächlich aus diesem Trick übernommen
// https://friendsofredaxo.github.io/tricks/addons/structure/be_yform_meta_ajax

class rex_api_new_ra_tag extends rex_api_function
{
    protected $published = true;

    function execute()
    {
        $tag_table = 'rex_ra_tags';  // Hier den Namen der Tag-Tabelle eintragen!
        // Parameter abrufen und auswerten
        $tag 	= rex_request( 'tag','string','' );
        $code = 0; // 1 = eingefügt, 2 = gefunden

		// Alle Parameter prüfen ob gesetzt
        if ( !$tag )
        {
            header( 'HTTP/1.1 400 Bad Request' );
            header( 'Content-Type: application/json; charset=UTF-8' );
            $result = [ 'errorcode' => 1, 'message' => 'A parameter is missing' ];
            exit( json_encode( $result ) );
        }


		//// SQL-OPTION //////
		$sql = rex_sql::factory();
		$sql->setDebug(false);
		$sql->setTable($tag_table);
        $sql->setWhere('tag_1 = :tag',['tag'=>$tag]);
        $sql->select();
        $res = $sql->getArray();

        // Hier wird unterschieden, ob es den gesuchten Tag bereits gibt.
        if ($res) {
            // wenn er bereits vorhanden ist, wird die passende Id und der code=2 zurückgegeben
            $lastId = $res[0]['id'];
            $code = 2;
        } else {
            // wenn nicht, wird er neu angelegt, die Id und der code = 1
            $sql->setTable($tag_table);
            $sql->setValue('tag_1', $tag);
            $sql->insert();
            $lastId = $sql->getLastId();
            $code = 1;
        }

        // Inhalt zusammenbauen
        $content = [ 'code' => $code, 'message' => 'success', 'dataId' => $lastId ];

        // Inhalt ausgeben
        header( 'Content-Type: application/json; charset=UTF-8' );
        exit( json_encode( $content ) );
    }
}

?>