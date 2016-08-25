<?

namespace Local\Api;

use Local\Direct\Import;

class v1 extends Api
{
	protected function import($args) {
		$method = $args[0];
		if ($method == 'campaign')
			return Import::campaign($this->post['client'], $this->post['id']);
		/*elseif ($method == 'public')
			return User::publicProfileFull($args[1], $args[2], $this->request);
		elseif ($method == 'nickname')
			return User::nickname($this->post['nickname']);
		elseif ($method == 'update')
			return User::update($this->post);
		elseif ($method == 'follow')
			return User::follow($this->post['publisher']);
		elseif ($method == 'unfollow')
			return User::unfollow($this->post['publisher']);
		elseif ($method == 'favorite')
		{
			if ($args[1] == 'add')
				return User::addToFavorite($this->post['ad']);
			elseif ($args[1] == 'remove')
				return User::removeFromFavorite($this->post['ad']);
			elseif ($args[1] == 'list')
				return User::favorites($this->request);
			elseif ($args[1] == 'count')
				return User::favoritesCount();
			else
				throw new ApiException(['wrong_endpoint'], 404);
		}
		elseif ($method == 'search')
			return User::search($this->request);
		elseif ($method == 'news')
			return News::getAppData($this->request);
		elseif ($method == 'myads')
			return User::getMyAds($args[1], $this->request);
		elseif ($method == 'support')
			return User::message($this->post['message']);
		elseif ($method == 'supportchat')
			return User::chat($this->request);*/
		else
			throw new ApiException('wrong_endpoint', 404);
	}

}