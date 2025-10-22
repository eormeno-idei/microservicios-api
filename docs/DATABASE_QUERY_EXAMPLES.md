# 🔍 Ejemplos de Consultas - Datos del Sistema

Este documento contiene ejemplos prácticos de cómo consultar y trabajar con los datos generados por los seeders.

---

## 📋 Índice

1. [Consultas Básicas](#consultas-básicas)
2. [Consultas con Relaciones](#consultas-con-relaciones)
3. [Consultas con Filtros](#consultas-con-filtros)
4. [Consultas Agregadas](#consultas-agregadas)
5. [Ejemplos con Tinker](#ejemplos-con-tinker)

---

## 🔤 Consultas Básicas

### Obtener todos los usuarios

```php
$users = User::all();

// Con paginación
$users = User::paginate(10);
```

### Obtener usuario por email

```php
$admin = User::where('email', 'admin@example.com')->first();
```

### Obtener usuarios con un rol específico

```php
$admins = User::role('admin')->get();
$users = User::role('user')->get();
```

### Obtener todos los canales

```php
$channels = Channel::all();
```

### Obtener canales por tipo

```php
use App\Enums\ChannelType;

$departments = Channel::where('type', ChannelType::DEPARTMENT)->get();
$institutes = Channel::where('type', ChannelType::INSTITUTE)->get();
```

### Obtener medios activos

```php
$activeMedias = Media::where('is_active', true)->get();

// Usando el scope
$activeMedias = Media::active()->get();
```

### Obtener posts por estado

```php
use App\Enums\PostStatus;

$drafts = Post::where('status', PostStatus::DRAFT)->get();
$approved = Post::where('status', PostStatus::APPROVED_BY_MODERATOR)->get();
$scheduled = Post::where('status', PostStatus::SCHEDULED)->get();
```

### Obtener posts por tipo

```php
use App\Enums\PostType;

$videos = Post::where('type', PostType::VIDEO)->get();
$images = Post::where('type', PostType::IMAGE)->get();
$multimedia = Post::where('type', PostType::MULTIMEDIA)->get();
```

---

## 🔗 Consultas con Relaciones

### Posts de un usuario con sus canales y medios

```php
$user = User::find(1);

// Obtener posts del usuario
$posts = $user->posts;

// Con eager loading (mejor performance)
$user = User::with('posts.channels', 'posts.medias')->find(1);
$posts = $user->posts;

foreach ($posts as $post) {
    echo "Post: {$post->name}\n";
    echo "Canales: " . $post->channels->pluck('name')->join(', ') . "\n";
    echo "Medios: " . $post->medias->pluck('name')->join(', ') . "\n\n";
}
```

### Canales de un usuario

```php
$user = User::find(1);
$channels = $user->channels;

// Ver nombres de canales
$channelNames = $user->channels->pluck('name')->toArray();
```

### Posts de un canal con sus medios

```php
$channel = Channel::find(1);
$posts = $channel->posts()->with('medias', 'user')->get();

foreach ($posts as $post) {
    echo "Post: {$post->name}\n";
    echo "Autor: {$post->user->name}\n";
    echo "Medios: {$post->medias->count()}\n\n";
}
```

### Attachments de un post

```php
$post = Post::with('attachments')->find(1);

foreach ($post->attachments as $attachment) {
    echo "Archivo: {$attachment->path}\n";
    echo "Tipo: {$attachment->mime_type}\n";
    echo "URL: {$attachment->url}\n\n";
}
```

### Posts distribuidos en un medio específico

```php
$media = Media::find(1);
$posts = $media->posts()->with('user', 'channels')->get();

echo "Posts para {$media->name}:\n\n";
foreach ($posts as $post) {
    echo "- {$post->name} (por {$post->user->name})\n";
}
```

---

## 🎯 Consultas con Filtros

### Posts programados para los próximos 7 días

```php
use Carbon\Carbon;

$upcomingPosts = Post::where('status', PostStatus::SCHEDULED)
    ->whereBetween('scheduled_at', [
        Carbon::now(),
        Carbon::now()->addDays(7)
    ])
    ->with('channels', 'medias', 'user')
    ->get();
```

### Posts aprobados con video

```php
$videoPosts = Post::where('status', PostStatus::APPROVED_BY_MODERATOR)
    ->where('type', PostType::VIDEO)
    ->with('attachments')
    ->get();
```

### Usuarios asignados a un canal específico

```php
$channel = Channel::find(1);
$users = $channel->users()->with('roles')->get();

echo "Usuarios en {$channel->name}:\n";
foreach ($users as $user) {
    $role = $user->roles->first()->name ?? 'sin rol';
    echo "- {$user->name} ({$role})\n";
}
```

### Medios de redes sociales activos

```php
use App\Enums\MediaType;

$socialMedias = Media::where('type', MediaType::SOCIAL_MEDIA)
    ->where('is_active', true)
    ->get();

// Usando scope
$socialMedias = Media::active()
    ->ofType(MediaType::SOCIAL_MEDIA)
    ->get();
```

### Posts con deadline próximo

```php
$urgentPosts = Post::whereNotNull('deadline')
    ->where('deadline', '<=', Carbon::now()->addDays(3))
    ->where('status', '!=', PostStatus::ARCHIVED)
    ->orderBy('deadline', 'asc')
    ->get();
```

---

## 📊 Consultas Agregadas

### Contar posts por estado

```php
use Illuminate\Support\Facades\DB;

$postsByStatus = Post::select('status', DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get()
    ->map(function($item) {
        return [
            'status' => $item->status->label(),
            'total' => $item->total
        ];
    });
```

### Contar posts por tipo

```php
$postsByType = Post::select('type', DB::raw('count(*) as total'))
    ->groupBy('type')
    ->get()
    ->map(function($item) {
        return [
            'type' => $item->type->label(),
            'total' => $item->total
        ];
    });
```

### Usuario con más posts

```php
$topUser = User::withCount('posts')
    ->orderBy('posts_count', 'desc')
    ->first();

echo "{$topUser->name} tiene {$topUser->posts_count} posts\n";
```

### Canal con más posts

```php
$topChannel = Channel::withCount('posts')
    ->orderBy('posts_count', 'desc')
    ->first();

echo "{$topChannel->name} tiene {$topChannel->posts_count} posts\n";
```

### Medio más utilizado

```php
$topMedia = Media::withCount('posts')
    ->orderBy('posts_count', 'desc')
    ->first();

echo "{$topMedia->name} distribuye {$topMedia->posts_count} posts\n";
```

### Posts con más attachments

```php
$postsWithAttachments = Post::withCount('attachments')
    ->having('attachments_count', '>', 0)
    ->orderBy('attachments_count', 'desc')
    ->get();
```

### Estadísticas generales

```php
$stats = [
    'total_users' => User::count(),
    'total_posts' => Post::count(),
    'total_channels' => Channel::count(),
    'total_medias' => Media::count(),
    'total_attachments' => Attachment::count(),
    'active_medias' => Media::where('is_active', true)->count(),
    'scheduled_posts' => Post::where('status', PostStatus::SCHEDULED)->count(),
    'approved_posts' => Post::where('status', PostStatus::APPROVED_BY_MODERATOR)->count(),
];
```

---

## 💻 Ejemplos con Tinker

Puedes ejecutar estos comandos en `php artisan tinker`:

### Ver todos los canales

```php
php artisan tinker
>>> Channel::all()->pluck('name', 'type')
```

### Ver posts con sus relaciones

```php
>>> Post::with('user', 'channels', 'medias')->get()->map(function($p) {
    return [
        'name' => $p->name,
        'author' => $p->user->name,
        'channels' => $p->channels->count(),
        'medias' => $p->medias->count(),
    ];
})
```

### Ver usuarios con sus canales

```php
>>> User::with('channels')->get()->map(function($u) {
    return [
        'name' => $u->name,
        'channels' => $u->channels->pluck('name'),
    ];
})
```

### Ver medios por tipo

```php
>>> Media::all()->groupBy('type')->map(function($group, $type) {
    return [
        'type' => $type,
        'count' => $group->count(),
        'names' => $group->pluck('name'),
    ];
})
```

### Ver posts programados

```php
>>> Post::where('status', 'scheduled')->get()->map(function($p) {
    return [
        'name' => $p->name,
        'scheduled' => $p->scheduled_at->format('Y-m-d H:i'),
        'channels' => $p->channels->pluck('name'),
    ];
})
```

### Ver attachments por tipo de archivo

```php
>>> Attachment::all()->groupBy('file_type')->map(function($group, $type) {
    return [
        'type' => $type,
        'count' => $group->count(),
    ];
})
```

---

## 🔧 Consultas Avanzadas

### Posts con todos sus datos relacionados

```php
$post = Post::with([
    'user' => function($query) {
        $query->with('roles');
    },
    'channels',
    'medias' => function($query) {
        $query->where('is_active', true);
    },
    'attachments'
])->find(1);

// Acceder a los datos
echo "Post: {$post->name}\n";
echo "Autor: {$post->user->name}\n";
echo "Rol del autor: {$post->user->roles->first()->name}\n";
echo "Estado: {$post->status->label()}\n";
echo "Tipo: {$post->type->label()}\n";
echo "\nCanales ({$post->channels->count()}):\n";
foreach ($post->channels as $channel) {
    echo "  - {$channel->name} ({$channel->type->label()})\n";
}
echo "\nMedios activos ({$post->medias->count()}):\n";
foreach ($post->medias as $media) {
    echo "  - {$media->name} ({$media->type->label()})\n";
}
echo "\nArchivos adjuntos ({$post->attachments->count()}):\n";
foreach ($post->attachments as $attachment) {
    echo "  - {$attachment->path} ({$attachment->file_type})\n";
}
```

### Buscar posts por contenido

```php
$searchTerm = 'conferencia';
$posts = Post::where('name', 'like', "%{$searchTerm}%")
    ->orWhere('content', 'like', "%{$searchTerm}%")
    ->with('user', 'channels')
    ->get();
```

### Posts aprobados listos para publicar

```php
$readyToPublish = Post::where('status', PostStatus::APPROVED_BY_MODERATOR)
    ->where(function($query) {
        $query->whereNull('scheduled_at')
              ->orWhere('scheduled_at', '<=', Carbon::now());
    })
    ->with('channels', 'medias')
    ->get();
```

### Canales sin posts

```php
$emptyChannels = Channel::doesntHave('posts')->get();
```

### Usuarios que no han creado posts

```php
$usersWithoutPosts = User::doesntHave('posts')->get();
```

### Medios inactivos

```php
$inactiveMedias = Media::where('is_active', false)->get();
```

---

## 📈 Dashboard de Estadísticas

```php
function getDashboardStats() {
    return [
        'overview' => [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'total_channels' => Channel::count(),
            'total_medias' => Media::active()->count(),
        ],
        'posts_by_status' => Post::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->total]),
        'posts_by_type' => Post::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->type->value => $item->total]),
        'channels_by_type' => Channel::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->type->value => $item->total]),
        'medias_by_type' => Media::select('type', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->type->value => $item->total]),
        'top_contributors' => User::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($user) => [
                'name' => $user->name,
                'posts' => $user->posts_count
            ]),
        'top_channels' => Channel::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($channel) => [
                'name' => $channel->name,
                'posts' => $channel->posts_count
            ]),
        'scheduled_posts_count' => Post::where('status', PostStatus::SCHEDULED)
            ->where('scheduled_at', '>', Carbon::now())
            ->count(),
        'urgent_posts' => Post::whereNotNull('deadline')
            ->where('deadline', '<=', Carbon::now()->addDays(3))
            ->where('status', '!=', PostStatus::ARCHIVED)
            ->count(),
    ];
}

// Usar en tinker o controller
$stats = getDashboardStats();
print_r($stats);
```

---

## 🎯 Comandos Útiles de Artisan

```bash
# Ver modelos y sus relaciones
php artisan tinker
>>> User::first()->posts
>>> Post::first()->channels
>>> Channel::first()->users

# Contar registros
>>> User::count()
>>> Post::count()
>>> Channel::count()

# Ver datos específicos
>>> User::find(1)->name
>>> Post::where('type', 'video')->count()
>>> Channel::where('type', 'department')->pluck('name')
```

---

## 📚 Recursos Adicionales

- [Documentación de Eloquent ORM](https://laravel.com/docs/eloquent)
- [Consultas con Query Builder](https://laravel.com/docs/queries)
- [Relaciones Eloquent](https://laravel.com/docs/eloquent-relationships)
- [Scopes en Laravel](https://laravel.com/docs/eloquent#query-scopes)

---

**Última actualización:** Octubre 2025  
**Versión:** 1.0.0
