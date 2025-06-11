---
title: Implement architecture decision records
date: 2020-06-25
area: core
tags: [adr, workflow]
---

## Context
We should document architecture and technical decisions for the shopware platform. The documentation should be easy to understand and easy to follow. The workflow for new decisions should add to our existing workflows and should not block the whole development process. One solution could be the form of architecture decision records (ADR) as described in the following articles:

*  [Documenting Architecture Decisions](http://thinkrelevance.com/blog/2011/11/15/documenting-architecture-decisions)
*  [A Simple but Powerful Tool to Record Your Architectural Decisions](https://medium.com/better-programming/here-is-a-simple-yet-powerful-tool-to-record-your-architectural-decisions-5fb31367a7da)

## Decision
We will record architecture decisions (ADR) in markdown files directly in the platform repository. The workflow for ADRs will be integrated in the existing merge request workflow. This has the following advantages:

*  Decision records are an integral part of the development process
*  Decisions remain in sync with the code itself
*  The Git history is also the decision history
*  Decisions are public available and accessible for every developer
*  Also external developers can add new ADRs via GitHub pull requests
*  Decision finding can be asynchronous via comments in the corresponding merge request

## Consequences
From now on, every architecture decision, affecting the shopware platform or one of its components, has to be recorded in an ADR, following the described workflow.

In the following you find answers to the most important questions about ADRs and the new workflow:

**Who can/must create ADRs?**   
Every developer working with shopware platform!

**When do you have to create an ADR?**  
Have you made a significant decision that impacts how developers should write code in the shopware platform? Write an ADR! Here are some cases, which can help you to understand when to write an ADR:
