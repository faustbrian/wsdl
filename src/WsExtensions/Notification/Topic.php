<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Notification;

/**
 * Represents a WS-Topics topic definition.
 *
 * Topics can have message types, subtopics, and form hierarchies.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Topic
{
    /**
     * @param string $name Topic name
     * @param array<int, string> $messageTypes QNames of message types
     * @param array<int, Topic> $children Subtopics
     */
    public function __construct(
        private readonly string $name,
        private array $messageTypes = [],
        private array $children = [],
    ) {}

    /**
     * Add a message type to this topic.
     */
    public function addMessageType(string $messageType): self
    {
        $this->messageTypes[] = $messageType;

        return $this;
    }

    /**
     * Add a subtopic to this topic.
     */
    public function addChild(Topic $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, string>
     */
    public function getMessageTypes(): array
    {
        return $this->messageTypes;
    }

    /**
     * @return array<int, Topic>
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
