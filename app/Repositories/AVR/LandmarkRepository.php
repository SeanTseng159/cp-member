<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\AVR;


use App\Enum\AVRImageType;
use App\Helpers\AVRImageHelper;
use App\Helpers\CommonHelper;
use App\Models\AVR\Landmark;
use App\Models\AVR\LandmarkCategory;
use App\Repositories\BaseRepository;
use App\Traits\MapHelper;

class LandmarkRepository extends BaseRepository
{
    use MapHelper;

    protected $landmarkModel = null;
    protected $landmarkCategoryModel = null;


    public function __construct(Landmark $landmark, LandmarkCategory $landmarkCategory)
    {
        $this->landmarkCategoryModel = $landmarkCategory;
        $this->landmarkModel = $landmark;
    }

    public function aroundPlace($lat, $lng, $distance)
    {
        $landmarks = $this->landmarkModel->where('status', 1)->get();
        $ret = [];
        foreach ($landmarks as $landmark) {
            $diff = $this->calcDistance($landmark->latitude, $landmark->longitude, $lat, $lng, 1, 2);
            if ($diff <= $distance) {
                $data = new \stdClass();
                $data->id = $landmark->id;
                $data->name = $landmark->name;
                $data->iconId = $landmark->landmark_category_id;
                $data->lat = $landmark->latitude;
                $data->lng = $landmark->longitude;
                $data->isInfo = (bool)$landmark->is_intro;
                $ret[] = $data;
            }
        }
        return $ret;
    }

    public function placeInfo($id)
    {
        $landmark = $this->landmarkModel->where('status', 1)->where('id', $id)->first();
        if ($landmark) {

            $data = new \stdClass();
            $data->id = $landmark->id;
            $data->name = $landmark->name;
            $data->iconId = $landmark->landmark_category_id;
            $data->address = $landmark->zipcode . ' ' . $landmark->county . $landmark->district . $landmark->address;
            $data->photo = AVRImageHelper::getImageUrl(AVRImageType::landmark, $landmark->id);
            $data->description = $landmark->intro;
            return $data;
        }
        return null;
    }

    public function icons($hash = null)
    {
        $allIcons = $this->landmarkCategoryModel->all();

        $data = [];
        foreach ($allIcons as $icon) {
            $item = new \stdClass();
            $item->id = $icon->id;
            $item->name = $icon->name;
            $item->iconUrl = AVRImageHelper::getImageUrl(AVRImageType::landmark_category, $icon->id);
//            $item->iconBase64 = base64_encode(@file_get_contents($item->iconUrl));
            $data[] = $item;
        }


        $dbHash = md5(serialize($data));

        if ($dbHash == $hash) {
            return null;
        } else {
            $ret = new \stdClass();
            $ret->hash = $dbHash;
            foreach ($data as $icon) {
                $icon->iconBase64 = base64_encode(@file_get_contents($icon->iconUrl));
                $ret->icons = $data;
            }
        }
        return $ret;
    }


}