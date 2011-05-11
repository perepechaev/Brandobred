<?php

class BrandTerms
{
    
    static public function status(Brand $brand){
        $title = html($brand->title);
        $terms = array(
            Status::APPROVE     => sprintf("Бренд &bdquo;%s&rdquo; принят", $title),
            Status::DISAPPROVE  => sprintf("Бренд &bdquo;%s&rdquo; отклонен", $title),
            Status::DIRTY       => sprintf("Бренд &bdquo;%s&rdquo; ожидает модерации", $title)
        );
        
        return $terms[$brand->status];
    }
    
}

?>