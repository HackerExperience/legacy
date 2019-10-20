<?php

// 2019: Pretty sure this was never actually used

class Versioning{

    private $pdo;

    public function __construct(){

        
        $this->pdo = $pdo;

    }

    public function listChanges(){

        $sqlSelect = "SELECT text FROM changelog";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if(count($data) == '0'){

            echo 'No changes';

        } else {

            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate('', 'changelog', '10', 'page=changelog&tab', '');
            $pagination->showPages('10', 'page=changelog&tab');

        }

    }

    public function showChange($id){

        if(self::issetChange($id)){

            $sqlQuery = "SELECT id, author, dateCreated, description, text FROM changelog WHERE id = $id LIMIT 1";
            $sqlInfo = $this->pdo->query($sqlQuery);
            $showInfo = $sqlInfo->fetch(PDO::FETCH_OBJ);

            echo "<table border=\"1\">";
            echo "<tr><td>ID</td><td>Date</td><td>Title</td><td>Author</td></tr>";
            echo "<tr>
                    <td>$showInfo->id</td>
                    <td>$showInfo->datecreated</td>
                    <td><a href=\"about?page=changelog&id=$showInfo->id\">$showInfo->description</a></td>
                    <td>$showInfo->author</td>
                    </tr>
                    </table><br/>";

            echo "Description: $showInfo->text";
            
            echo "<br/><br/><a href=\"about?page=changelog\">Back</a>";


        } else {

            echo 'This change doesnt exists.';

        }

    }

    private function issetChange($id){

        $sqlSelect = "SELECT text FROM changelog WHERE id = $id";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if(count($data) == '1'){
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public function listBugReports(){

        $sqlSelect = "SELECT id FROM bugreports";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if(count($data) == '0'){

            echo 'No bugs were reported.';

        } else {

            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate('', 'bugs', '10', 'page', '');
            $pagination->showPages('10', 'page');

        }

    }

    public function showBugReport($id){

        if(self::issetBug($id)){

            $sqlQuery = "SELECT * FROM bugreports WHERE id = $id LIMIT 1";
            $sqlInfo = $this->pdo->query($sqlQuery);
            $showInfo = $sqlInfo->fetch(PDO::FETCH_OBJ);

            echo "<table border=\"1\">";
            echo "<tr><td>ID</td><td>Date</td><td>Link</td><td>Reported by</td></tr>";
            echo "<tr>
                    <td>$showInfo->id</td>
                    <td>$showInfo->datecreated</td>
                    <td>$showInfo->buglink</td>
                    <td>$showInfo->bugreporter</td>
                    </tr>
                    </table><br/>";

            echo "Description: $showInfo->bugtext <br/><br/>";

            if($showInfo->reviewed == '1'){

                echo "Comment of author: $showInfo->comment";

            } else {

                echo "Bug not yet viewed";

            }

            echo "<br/><br/><a href=\"bugs\">Back</a>";


        } else {

            echo 'This bug doesnt exists.';

        }

    }

    private function issetBug($id){

        $sqlSelect = "SELECT id FROM bugreports WHERE id = $id";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if(count($data) == '1'){
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public function showBugReportForm(){

        echo 'Report new bug:';

        ?>

        <form action="bugAdd" method="POST">

            <input type="hidden" name="bugRep" value="<?php echo $_SESSION['id']; ?>">
            Bug description <textarea name="bugDesc" rows="5" cols="60"></textarea><br/>
            Bug link <input type="text" size="10" name="bugLink"><br/>
            <input type="submit" value="Report this bug">

        </form>

        <?php

    }

    public function newBugReport($id, $text, $link){

        $sqlQuery = "INSERT INTO bugreports (id, bugLink, bugReporter, bugText, comment, reviewed, dateCreated) VALUES ('', ?, ?, ?, '', '0', NOW())";
        $sqlReg = $this->pdo->prepare($sqlQuery);
        $sqlReg->execute(array($link, $id, $text));

    }

}

?>