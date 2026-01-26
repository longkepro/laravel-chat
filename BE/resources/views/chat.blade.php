<x-chat-layout> 
  <div class="w-screen h-screen bg-white shadow-lg rounded-lg flex flex-col">

    <!-- header -->
    <x-chat-header/>

    <!-- Chatting -->
    <div class="flex flex-row bg-white h-screen ">

      <!-- chat list -->
      <div class="flex flex-col w-2/5 border-r-2 overflow-y-auto" id="users">
        <!-- search compt -->
        <x-search-compt>
            search chatting
        </x-search-compt>

        <!-- user list -->
        @foreach ($users as $user )
          @if($user->id !== auth()->id())
            <x-users-list-component :user="$user"></x-users-list-component>
          @endif
        @endforeach
      </div>
      <!-- end chat list -->

      <!-- chat box -->
      <div class="w-full px-5 py-5 flex flex-col justify-between h-[650px]">
        
        <div class="flex-1 flex-col gap-2 justify-start gap-2 mb-10 overflow-y-auto" id="messages">
        </div>

        <form class="flex items-center justify-between w-full py-3 px-3 h-16 gap-2 right-0">
            <input
              id="message"
              type="text"
              placeholder="Type your message here..."
              class="w-full bg-gray-200 py-2 px-3 rounded-xl focus:outline-none"
            />
            <button
              id="send"
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded-xl hover:bg-blue-600"
            >
              Send
            </button>
        </form>
      </div>
      <!-- end chat box -->

      </div>
    </div>
  </div>  
</div>
</x-chat-layout>

