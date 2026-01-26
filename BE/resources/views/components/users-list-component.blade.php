        <div {{$attributes -> merge(["class" => "user-card flex flex-row py-4 px-2 justify-center items-center border-b-2 hover:bg-gray-100 cursor-pointer",
        "id" => $user->id,//đặt id thẻ div <.user-card> trùng với id của user tương ứng với thẻ
        
        ])}}>
          <div class="w-1/4">
            <img
              src="{{ $user->avatar ?? 'https://source.unsplash.com/_7LbC5J-jw4/600x600'}}"

              class = "flex flex-row py-4 px-2 justify-center items-center border-b-2"
              alt=""
            />
          </div>

          <div class="w-full flex justify-between items-center">
            <div>
                <div class="text-lg font-semibold">{{ $user->username }}</div>
                <span class="{{ $user->unread ? 'font-bold text-black' : 'text-gray-500' }}">
                    {{ $user->lastMessage?->message ?? 'No messages yet' }}
                </span>
            </div>

            @if($user->unread)
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full"></span>
            @endif
        </div>
          
        </div>
        