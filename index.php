<?
//основа взята здесь: http://innvo.com/1315192249-css-sprites-with-php
class images_to_sprite {
    function images_to_sprite($folder,$output,$sort,$rows,$fname='',$lname='') {
        $this->folder = ($folder ? $folder : 'myfolder'); // Folder name to get images from, i.e. C:\\myfolder or /home/user/Desktop/folder
        $this->filetypes = array('jpg'=>true,'png'=>true,'jpeg'=>true,'gif'=>true); // Acceptable file extensions to consider
        $this->output = ($output ? $output : 'mysprite'); // Output filenames, mysprite.png and mysprite.css
        $this->files = array();
        $this->images = array(); //все характеристики изображений
        $this->sort = ($sort ? $sort : 0); //опция сортировки
        $this->rows = ($rows ? $rows : 1); //число строк изображений в спрайте
        $this->fname = $fname; //приставка к имени
        $this->lname = $lname; //окончание имени
    }

    function create_sprite() {

        $basedir = $this->folder;
        $files = array();

        // Read through the directory for suitable images
        //получение данных из папки с изображениями
        if($handle = opendir($this->folder)) {
            while (false !== ($file = readdir($handle))) {
                $split = explode('.',$file);
                // Ignore non-matching file extensions
                if($file[0] == '.' || !isset($this->filetypes[$split[count($split)-1]]))
                    continue;
                $output = getimagesize($this->folder.'/'.$file);
                $this->files[$file] = $file;
                $this->images[$file] = array('width'=>$output[0], 'height'=>$output[1], 'fname'=>$file, 'sname'=>$split[0]); //ширина, высота, полное имя, сокращенное имя (без разширения), пример: Array ( [arrow-up.png] => Array ( [width] => 28 [height] => 28 [fname] => arrow-up.png [sname] => arrow-up )
            }
            closedir($handle);
        }

        //print_r($this->images);
        //var_dump($this->files);
        //print_r($this->files);
        echo '<br>';

        $totalWidth=0; //общая ширина изображений
        $maxWidth=0; //максимальная ширина изображений
        $maxHeight=0; //максимальная высота изображений
        $totalHeight=0; //общая высота изображений
        foreach($this->images as $file=>$size){
            $totalWidth+=$size['width'];
            $totalHeight+=$size['height'];
            if($maxHeight<$size['height'])
                $maxHeight=$size['height'];
            if($maxWidth<$size['width'])
                $maxWidth=$size['width'];
        }

        if ($this->sort==2){
        //сортируем изображения по убыванию высоты пользовательской функцией
            function custom_sort($a, $b)
            {
                return strnatcmp($b["height"], $a["height"]); //для сортировки в обратном порядке порядок аргументов такой
            }
            uasort($this->images, "custom_sort");
        }
        elseif($this->sort==1){
            function custom_sort($a, $b)
            {
                return strnatcmp($a["height"], $b["height"]); //сортировка по возростанию
            }
            uasort($this->images, "custom_sort");
        }

        //echo $totalWidth;
        //print_r($this->images);

        //выстроим макет по горизонтали
        $im = imagecreatetruecolor($totalWidth,$maxHeight);

        //альфа-канал
        imagesavealpha($im, true);
        $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im,0,0,$alpha);

        //print_r($this->files);

        //добавление изображений на спрайт и генерация CSS


        $fp = fopen($this->output.'.css','w');
        //счетчик текущей позиции (расчитываются наоборот для CSS/спрайта)
        $nowPosCss=array('h'=>0, 'v'=>0); //горизонталь, вертикаль, для CSS
        $nowPosSpt=array('h'=>0, 'v'=>0); //горизонталь, вертикаль, для спрайта

        //собираем изображения из папки отписываемся в CSS
        //список новых блоков
        $sClasses='';
        $num=0;
        $count=count($this->images);
        echo 'число изображений: '.$count.'</br>';
        foreach($this->images as $img=>$val){
            $num++;
            $sClasses.='.'.$val['sname'];
            if($num<$count)
                $sClasses.=', ';
        }
        //css-файл
        fwrite($fp, $sClasses.' { background-image: url("'.$this->output.'.png"); text-align:center; display: inline-block;}'."\n");
        $sPosCssH=''; //для валидности CSS не пишем 'px' с нулем в background-position
        $sPosCssV='';
        $nSpacing = 5; //отступы в спрайте
        foreach($this->images as $img=>$val){
            $sPosCssH = ($nowPosCss['h']!=0) ? $nowPosCss['h'].'px' : $nowPosCss['h'];
            $sPosCssV = ($nowPosCss['v']!=0) ? $nowPosCss['v'].'px' : $nowPosCss['v'];
            fwrite($fp,'.'.$val['sname'].' {background-position: ' . $sPosCssH .' '. $sPosCssV . ' ; width: '.$val['width'].'px; height: '.$val['height'].'px}'."\n");
            $im2 = imagecreatefrompng($this->folder.'/'.$val['fname']);
            imagecopy($im,$im2,$nowPosSpt['h'],$nowPosSpt['v'],0,0,$val['width'],$val['height']);
            $nowPosSpt['h']+=$val['width'] + $nSpacing;
            $nowPosCss['h']-=$val['width'] + $nSpacing;
        }
        fclose($fp);
        imagepng($im,$this->output.'.png'); // Save image to file
        imagedestroy($im);

        //проверяем на ходу
        if(file_exists($this->output.'.css')){
            echo '<link rel="stylesheet" type="text/css" href="'.$this->output.'.css">';
            //показываем готовые блоки
            foreach ($this->images as $img=>$val){
                echo '<div class="'.$val['sname'].'"></div> -' .$val['fname'].'<br>';
            }
        }
        else{
            echo 'спрайт не создан';
        }
    }


}
class multi_sprite{
    function multi_sprite($folder,$output,$sort,$rows,$fname='',$lname='') {
        $this->folder = ($folder ? $folder : 'myfolder'); // Folder name to get images from, i.e. C:\\myfolder or /home/user/Desktop/folder
        $this->filetypes = array('jpg'=>true,'png'=>true,'jpeg'=>true,'gif'=>true); // Acceptable file extensions to consider
        $this->output = ($output ? $output : 'mysprite'); // Output filenames, mysprite.png and mysprite.css
        $this->files = array();
        $this->images = array(); //все характеристики изображений
        $this->sort = ($sort ? $sort : 0); //опция сортировки
        $this->rows = ($rows && $rows!=0 ? $rows : 1); //число строк изображений в спрайте
        $this->fname = $fname; //приставка к имени
        $this->lname = $lname; //окончание имени
        $this->space = 5; //промежутки между картинками в спрайте
    }

    function create_sprite() {
        $basedir = $this->folder;
        $files = array();

        // Read through the directory for suitable images
        //получение данных из папки с изображениями
        if($handle = opendir($this->folder)) {
            while (false !== ($file = readdir($handle))) {
                $split = explode('.',$file);
                // Ignore non-matching file extensions
                if($file[0] == '.' || !isset($this->filetypes[$split[count($split)-1]]))
                    continue;
                $output = getimagesize($this->folder.'/'.$file);
                $this->files[$file] = $file;
                $this->images[$file] = array('width'=>$output[0], 'height'=>$output[1], 'fname'=>$file, 'sname'=>$split[0]); //ширина, высота, полное имя, сокращенное имя (без разширения), пример: Array ( [arrow-up.png] => Array ( [width] => 28 [height] => 28 [fname] => arrow-up.png [sname] => arrow-up )
            }
            closedir($handle);
        }

        //print_r($this->images);
        //var_dump($this->files);
        //print_r($this->files);
        echo '<br>';

        $totalWidth=0; //общая ширина изображений
        $maxWidth=0; //максимальная ширина изображений
        $maxHeight=0; //максимальная высота изображений
        $totalHeight=0; //общая высота изображений
        foreach($this->images as $file=>$size){
            $totalWidth+=$size['width']+$this->space;
            $totalHeight+=$size['height']+$this->space;
            if($maxHeight<$size['height'])
                $maxHeight=$size['height']+$this->space;
            if($maxWidth<$size['width'])
                $maxWidth=$size['width']+$this->space;
        }

        if ($this->sort==2){
            //сортируем изображения по убыванию высоты пользовательской функцией
            function custom_sort($a, $b)
            {
                return strnatcmp($b["height"], $a["height"]); //для сортировки в обратном порядке порядок аргументов такой
            }
            uasort($this->images, "custom_sort");
        }
        elseif($this->sort==1){
            function custom_sort($a, $b)
            {
                return strnatcmp($a["height"], $b["height"]); //сортировка по возростанию
            }
            uasort($this->images, "custom_sort");
        }
        //***********
        //рассчеты для расстановки в несколько рядов
        /*Суть алгоритма:
        1. Ширину одного ряда картинок для спрайта находим путем вычисления максимальной ширины изображения или доли общей ширины от указанного числа строк (что больше)
        2. Определяем, какие изображения войдут в текущий ряд, ширину и высоту ряда
        3. Составляем спрайт из полученных координат
        */
        //прикидываем размеры спрайта

        //максимальная ширина ряда, ширина общего спрайта
        $rowWidth=0;
        foreach($this->images as $img=>$size){
            if($rowWidth>0 && $rowWidth>=$totalWidth/$this->rows)
                break;
            else $rowWidth+=$size['width']+$this->space;
        }

        //счетчики
        $nowPosSpt=array('h'=>0, 'v'=>0); //горизонталь, вертикаль для общего спрайта, счетчик координат
        $positions=array(); //изображения с шириной и координатами для формирования общего спрайта
        $sptHeight=array(); //счетчик высоты общего спрайта, массив для возможности счета отдельно для каждого ряда
        $maxHeight=0;
        $nowRow=0;

        //формирование нового массива изображений с позициями
        foreach($this->images as $img=>$size){

            $positions[$img]=['sname'=>$size['sname'], 'h'=>$nowPosSpt['h'], 'v'=>$nowPosSpt['v'], 'width'=>$size['width'], 'height'=>$size['height']];

            //высота ряда (найдем из текущих максимально высоких, поместившихся в ряд)
            if($maxHeight<$size['height'])
                $maxHeight=$sptHeight[$nowRow]=$size['height']+$this->space;

            $nowPosSpt['h']+=$size['width']+$this->space;

            if ($nowPosSpt['h']>=$rowWidth || $rowWidth-$nowPosSpt['h']<$size['width']){ //переходим на следующий ряд, если следующая позиция больше или равна ширине ряда или нет места для текущего изображения
                $nowRow++;
                $nowPosSpt['h']=0+$this->space;
                $nowPosSpt['v']+=$maxHeight;
                $sptHeight[$nowRow]+=$maxHeight+$this->space;
            }
        }

        /*print_r($positions);
        echo '<br>';
        echo $maxHeight;
        echo '<br>';
        echo print_r($sptHeight);
        echo '<br>';
        echo $rowWidth;
        echo $rowWidth;*/
        if($this->rows<=1) $im = imagecreatetruecolor($totalWidth,$maxHeight);
        else $im = imagecreatetruecolor($rowWidth,array_sum($sptHeight));

        //альфа-канал
        imagesavealpha($im, true);
        $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im,0,0,$alpha);

        $fp = fopen($this->output.'.css','w');
        //счетчик текущей позиции (расчитываются наоборот для CSS/спрайта)
        $nowPosCss=array('h'=>0, 'v'=>0); //горизонталь, вертикаль, для CSS

        //собираем изображения из папки отписываемся в CSS
        //список новых блоков
        $sClasses='';
        $num=0;
        $count=count($this->images);
        echo 'число изображений: '.$count.'</br>';
        foreach($this->images as $img=>$val){
            $num++;
            $sClasses.='.'.$this->fname.$val['sname'].$this->lname;
            if($num<$count)
                $sClasses.=', ';
        }
        //css-файл
        fwrite($fp, $sClasses.' { background-image: url("'.$this->output.'.png"); text-align:center; display: inline-block;}'."\n");
        $sPosCssH=''; //для валидности CSS не пишем 'px' с нулем в background-position
        $sPosCssV='';
        foreach($positions as $img=>$val){
            $nowPosCss['h']=$val['h'];
            $nowPosCss['v']=$val['v'];
            $sPosCssH = ($nowPosCss['h']!=0) ? $nowPosCss['h']*(-1).'px' : $nowPosCss['h'];
            $sPosCssV = ($nowPosCss['v']!=0) ? $nowPosCss['v']*(-1).'px' : $nowPosCss['v'];
            fwrite($fp,'.'.$this->fname.$val['sname'].$this->lname.' {background-position: ' . $sPosCssH .' '. $sPosCssV . ' ; width: '.$val['width'].'px; height: '.$val['height'].'px}'."\n");
            $im2 = imagecreatefrompng($this->folder.'/'.$img);
            imagecopy($im,$im2,$val['h'],$val['v'],0,0,$val['width'],$val['height']);
        }
        fclose($fp);
        imagepng($im,$this->output.'.png'); // Save image to file
        imagedestroy($im);

        //проверяем на ходу
        if(file_exists($this->output.'.css')){
            echo '<link rel="stylesheet" type="text/css" href="'.$this->output.'.css">';
            //показываем готовые блоки
            foreach ($positions as $img=>$val){
                echo '<div class="'.$this->fname.$val['sname'].$this->lname.'"></div> -' .$img.'<br>';
            }
        }
        else{
            echo 'спрайт не создан';
        }

    }
}

//простой спрайт в один горизонтальный ряд
//$class = new images_to_sprite('img','sprite'.time(),1,2); //папка с изображениями, имя спрайта и css-файла, сортировка картинок по возростанию высоты(1) или убыванию (2)
//$class->create_sprite();

//сложный спрайт в несколько рядов
$class = new multi_sprite('img','sprite_lang_flags',1,1,'');
$class->create_sprite();

/*Известные проблемы:
1. Спрайт кэшируется, так что при смене набора желательно менять имя спрайта
*/

?>
