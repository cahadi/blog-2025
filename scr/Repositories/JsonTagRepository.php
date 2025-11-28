<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\TagFactoryInterface;
use App\Interfaces\TagRepositoryInterface;
use App\Models\Tag;

class JsonTagRepository implements TagRepositoryInterface
{
    public function __construct(
        private TagFactoryInterface $factory,
        private string $file = ROOT_PATH . '/storage/tags.json'
    ) {}

    public function all(): array
    {
        $tags = $this->read();
        return array_map([$this->factory, 'create'], $tags);
    }

    public function find(int $id): ?Tag
    {
        $tags = $this->read();
        foreach ($tags as $tag)
        {
            if ($tag['id'] === $id)
            {
                return $this->factory->create($tag);
            }
        }
        return null;
    }

    public function findByName(string $name): ?Tag
    {
        $tags = $this->read();
        foreach ($tags as $tag)
        {
            if ($tag['name'] === $name)
            {
                return $this->factory->create($tag);
            }
        }
        return null;
    }

    public function findBySlug(string $slug): ?Tag
    {
        $tags = $this->read();
        foreach ($tags as $tag)
        {
            if ($tag['slug'] === $slug)
            {
                return $this->factory->create($tag);
            }
        }
        return null;
    }

    public function create(array $data): Tag
    {
        $tags = $this->read();
        $id = empty($tags) ? 1 : max(array_column($tags, 'id')) + 1;
        $newTag = [
            'id' => $id,
            'name' => $data['name'] ?? '',
            'slug' => $data['slug'] ?? $this->generateSlug($data['name'] ?? ''),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $tags[] = $newTag;
        $this->write($tags);
        return $this->factory->create($newTag);
    }

    public function update(int $id, array $data): ?Tag
    {
        $tags = $this->read();
        foreach ($tags as &$tag)
        {
            if ($tag['id'] === $id)
            {
                $tag['name'] = $data['name'] ?? $tag['name'];
                $tag['slug'] = $data['slug'] ?? $this->generateSlug($data['name'] ?? '');
                $this->write($tags);
                return $this->factory->create($tag);
            }
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $tags = $this->read();
        $countBefore = count($tags);
        $tags = array_filter($tags, fn($tag) => $tag['id'] !== $id);
        if (count($tags) === $countBefore)
        {
            return false;
        }
        $this->write(array_values($tags));
        return true;
    }

    public function attachToPost(int $tagId, int $postId): bool
    {
        $postTags = $this->readPostTags();
        if (!isset($postTags[$postId]))
        {
            $postTags[$postId] = [];
        }
        if (!in_array($tagId, $postTags[$postId]))
        {
            $postTags[$postId][] = $tagId;
            $postTags[$postId] = array_unique($postTags[$postId]);
        }
        $this->writePostTags($postTags);
        return true;
    }

    public function detachFromPost(int $tagId, int $postId): bool
    {
        $postTags = $this->readPostTags();
        if (isset($postTags[$postId])) {
            $postTags[$postId] = array_filter($postTags[$postId], fn($id) => $id !== $tagId);
            $this->writePostTags($postTags);
            return true;
        }
    }

    public function getPostTags(int $postId): array
    {
        $postTags = $this->readPostTags();
        $tagIds = $postTags[$postId] ?? [];
        $tags = [];
        foreach ($tagIds as $tagId)
        {
            $tag = $this->find($tagId);
            if ($tag)
            {
                $tags[] = $tag;
            }
        }
        return $tags;
    }

    public function getPostsByTag(int $tagId): array
    {
        $postTags = $this->readPostTags();
        $posts = [];
        foreach ($postTags as $pId => $tIds)
        {
            if (in_array($tagId, $tIds))
            {
                $posts[] = $pId;
            }
        }
        return $posts;
    }

    private function read(): array
    {
        if (!file_exists($this->file))
        {
            $this->write([]);
        }
        return json_decode(file_get_contents($this->file), true) ?: [];
    }

    private function write(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function readPostTags(): array
    {
        $file = ROOT_PATH . '/storage/post_tags.json';
        if (!file_exists($file))
        {
            $this->writePostTags([]);
        }
        return json_decode(file_get_contents($file), true) ?: [];
    }

    private function writePostTags(array $data): void
    {
        $file = ROOT_PATH . '/storage/post_tags.json';
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function generateSlug(string $name): string
    {
        $transliterated = transliterator_transliterate('Russian-Latin/BGN; Any-Lower', $name);
        $slug = preg_replace('/[^a-z0-9]/', '_', $transliterated);
        return preg_replace('/_+/', '_', $slug);
    }
}