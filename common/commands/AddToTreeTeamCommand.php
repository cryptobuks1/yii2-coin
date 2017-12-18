<?php

namespace common\commands;

use Yii;
use yii\base\Object;
use common\models\Team;
use common\models\User;
use trntv\bus\interfaces\SelfHandlingCommand;

/**
 * @author 
 */
class AddToTreeTeamCommand extends Object implements SelfHandlingCommand
{
    /**
     * @var integer
     */
    public $user_id;
    /**
     * @var integer
     */
    public $related_id;

    /**
     * @param AddToTreeTeamCommand $command
     * @return bool
     */
    public function handle($command)
    {
        if(Team::find()->where(['user_id'=>$command->user_id,'related_id'=>$command->related_id])->exists()){
            return false;
        }
        
        $model = new Team();
        $model->user_id = $command->user_id;
        $model->related_id = $command->related_id;
        $model->level = 1;
        $model->save(false);
        $this->scanRelated($model->related_id,$model->user_id,$model->related_id);

        return $result;
    }
    
    private function scanRelated($related_id,$user_id,$primary){
        echo 'Scan....';
        // Find parent of related_id
        $user = User::findOne($user_id);
        if($user && $user->referrer){
            // Find level
            $team = Team::find()->where(['user_id'=>$user->referrer,'related_id'=>$user->id])->limit(1)->one();
            if($team){
                
                $model = new Team();
                $model->user_id = $user->referrer;
                $model->related_id = $primary;
                $model->level = $team->level + 1;
                $model->save();
                return $this->scanRelated($user->id,$user->referrer,$primary);
            }else{
                return false;
            }
            
        }
        return false;
        
    }
}