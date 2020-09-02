<?php
/**
 * Created by Fish.
 * 2019/12/23 4:44 下午
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Helpers\CommonHelper;
use Carbon\Carbon;

class MemberDiscountResult extends BaseResult
{

    public function listCanUsed($member_discounts,$cartItems,$memberID)
    {
        $resultArray = [];
        // dd(empty($member_discounts[0]->discountCode));
        foreach($member_discounts as $item){
            $addBo=True;
            $message='';
            
            //購物車裏面沒有商品
            if(empty($cartItems[0]->items)){
                $addBo=false;
                $message='沒有商品';
            }
            
            //discountCode 判斷　有效數量　　　優惠卷時間
            if($addBo){
                //如果沒有優惠倦就到下個loop
                if(empty($item->discountCode)){
                    continue;
                }elseif( $item->discountCode->discount_code_limit_count <= $item->discountCode->discount_code_used_count){
                    $addBo=false;
                    $message='優惠卷使用完畢';
                }
                
            }
            


            //拿出  discountCodeMember  =0 代表無上線
            if($addBo){
                if($item->discountCode->discount_code_member_use_count!=0){
                    //檢查式否達會員可使用上限 //有效數量
                    
                    //會員可使用上限
                    $filtered = collect($item->discountCodeMember)->filter(function ($item) use($memberID) {
                        return $item->member_id== $memberID;
                    });
    
                    if(collect($filtered)->count() >= $item->discountCode->discount_code_member_use_count){
                        $addBo=false;
                        $message='超過可使用上限';
                    }
                      
                }

                //首購
                if($item->discountCode->discount_first_type==1){
                    //檢查是否為首物購買
                    if(collect($item->discountCodeMember)->count()>0){
                        $addBo=false;
                        $message='非首購';
                    }  
                }

            }//end discountCodeMember
            
            //取出 discount_code_block_prods  
            //discount_code_tags with tag_prods 檢查是否可以使用範圍的商品
            if($addBo){
                //將優惠倦拒絕的prodId列出來 存在   $blockProdIdArray    
                $blockProdIdArray=[];       
                foreach($item->discountCodeBlock as $tmp){
                    $blockProdIdArray[]=$tmp->prod_id;
                }

                //將優惠倦接受prodId列出來 存在   $allowProdIdArray   
                $allowProdIdArray=[];
                foreach($item->discountCodeTag as $discountTag){
                    $tmpProdIdArray=collect(collect($discountTag)->get('tag_prod_id'))->pluck('prod_id')->all();
                    $allowProdIdArray=collect([$allowProdIdArray,$tmpProdIdArray])->collapse()->all();
                }

                
                //拿出購物車的商品
                foreach($cartItems[0]->items as $cartItem){
                    
                    $cartProdsTmp=new \stdClass;
                    //判斷商品是否再拒絕的裏面
                    if (in_array($cartItem->id, $blockProdIdArray)){
                        
                    //流下沒有背拒絕且接受的商品
                    }elseif(in_array($cartItem->id, $allowProdIdArray)){
                        $cartProdsTmp->prodId=$cartItem->id;
                        $cartProdsTmp->price=$cartItem->price;
                        $cartProdsTmp->qty=$cartItem->qty;
                        $cartProdsTmp->specId=$cartItem->additionals['specId'];
                        $cartProdsTmp->priceId=$cartItem->additionals['priceId'];
                        $cartProdsPass[]=$cartProdsTmp;
                    }
                    
                }
                
            }//end  discount_code_block_prods discount_code_tags


            if($addBo & empty($cartProdsPass)){
                $addBo=false;
                $message='商品不符合條件';
            }

            //dicount_codes 是否符合discount條件
            if($addBo ){
                $totalPrice=0;
                foreach($cartProdsPass as $cartTmp){
                    $totalPrice+=(Integer)$cartTmp->qty*(Integer)$cartTmp->price;
                }
                
                //滿足最低折扣 //最低限制消費
                //計算折扣
                if( $item->discountCode->discount_code_type ==1){
                    $totalPriceAfterDiscount=(Integer)($totalPrice*(Float)$item->discountCode->discount_code_price/100);
                //計算折價
                }elseif($item->discountCode->discount_code_type ==2){
                    $totalPriceAfterDiscount=$totalPrice-(Integer)$item->discountCode->discount_code_price;
                }
                
                
                if( $item->discountCode->discount_code_limit_price >$totalPriceAfterDiscount){
                    $addBo=false;
                    $message='需要滿足最低消費';
                }
                
                



            }//end dicount_codes 

            // dd($item->discountCode);
            $result=$this->getListResult($item,'null');
            $result->status=$addBo;
            $result->message=$message;
            // $result->id=$item->discountCode->discount_code_id;
            // $result->name=$item->discountCode->discount_code_name;
            // $result->value=$item->discountCode->discount_code_value;
            // $result->endTime=Carbon::parse($item->discountCode->discount_code_endtime)->format('Y-m-d');
            $addBo ? $resultArray['useful'][]=$result : $resultArray['useless'][]=$result;
        }//end foreach

        return $resultArray;
    }//end 


    public function list($datas,$func){

        $resultAraray=[];
        
        // dd($datas);
        // dd(collect($datas[1]->discountCode->discountCodeMember)->count());
        foreach($datas as $key=>$item){
            
            switch ($func){
                case 'current':
                    $count=collect($item->discountCode->discountCodeMember)->count();
                    if($item->discountCode->discount_code_member_use_count!=0 && $item->discountCode->discount_code_member_use_count <= $count ){
                        //超過使用上線
                    }elseif($count>0 && $item->discountCode->discount_first_type==1){
                        //只有給首次購買
                    }else{
                        $resultAraray[]=$this->getListResult($item,$func);
                    }
                    break;
                case 'disabled':
                    $resultAraray[]=$this->getListResult($item,$func);
                    
                    break;
                case 'used':
                    foreach($item->discountCodeMemberByMember as $tmp){
                        
                        $resultAraray[]=$this->getListResult($tmp,$func);
                    }
                    break;
            }

            
            
            
        }
        return $resultAraray;
    }

    public function getListResult($data,$func){
        $result=new \stdClass;
        $result->id=$data->discountCode->discount_code_id;
        $result->name=$data->discountCode->discount_code_name;
        $result->value=$data->discountCode->discount_code_value;
        $result->desc= $data->discountCode->discount_code_desc;
        $result->status=$func;
        $result->orderNo=$data->order_no;
        $result->endTime=Carbon::parse($data->discountCode->discount_code_endtime)->format('Y-m-d');
        //$result->range=Carbon::parse($data->discountCode->discount_code_starttime)->format('Y-m-d').'~'.Carbon::parse($data->discountCode->discount_code_endtime)->format('Y-m-d');
        $result->imageUrl=$this->getImg($data->discountCode->image_path);
        $tag='';
        foreach($data->discountCode->discountCodeTag as $item){
            $tag=$tag.$item->tag->tag_name.',';
        }
        $result->category=substr($tag,0,-1);
        return $result;
    }

    public function listByProd($member_discounts,$discountCodes){

        foreach($discountCodes as $itemDiscount){
            //是否有這張discount
            if(collect($member_discounts)->contains('discount_code_id',$itemDiscount->discount_code_id)){
                $ownStatus=true;
            }else{
                $ownStatus =false;
            }
            $resultObj=new \stdClass;
            $resultObj->id= $itemDiscount->discount_code_id;
            $resultObj->name= $itemDiscount->discount_code_name;
            $resultObj->value= $itemDiscount->discount_code_value;
            $resultObj->desc= $itemDiscount->discount_code_desc;
            $resultObj->range=Carbon::parse($itemDiscount->discount_code_starttime)->format('Y-m-d').'~'.Carbon::parse($itemDiscount->discount_code_endtime)->format('Y-m-d');
            $resultObj->imageUrl= $this->getImg($itemDiscount->image_path);
            $tag='';
            foreach($itemDiscount->discountCodeTag as $item){
                $tag=$tag.$item->tag->tag_name.',';
            }
            $resultObj->category=substr($tag,0,-1);
            $resultObj->ownStatus= $ownStatus;
      
            $result[]=$resultObj;


        }//end foreach
        return $result;
    }

    /**
     * 取得圖片
     * @param $imgs
     * @return string
     */
    private function getImg($imgs)
    {
        return isset($imgs) ? $this->backendHost . $imgs : '';
    }


}